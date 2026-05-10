<?php

namespace App\Http\Helpers\XMLSecLibs;

use DOMDocument;
use App\Http\Helpers\XMLSecLibs\XMLSecEnc;
use App\Http\Helpers\XMLSecLibs\XMLSecurityDSig;
use App\Http\Helpers\XMLSecLibs\XMLSecurityKey;
use RuntimeException;
use UnexpectedValueException;

/**
 * Class SignedXml
 */
class SignedXml
{
    /* Transform */
    const ENVELOPED = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';
    const EXT_NS = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';
    /**
     * Private key.
     *
     * @var string
     */
    protected $privateKey;

    /**
     * Public key.
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Signature algorithm URI. By default RSA with SHA256.
     *
     * @var string
     */
    protected $keyAlgorithm = XMLSecurityKey::RSA_SHA256;

    /**
     * Digest algorithm URI. By default SHA256.
     *
     * @var string
     *
     * @see AdapterInterface::SHA256
     */
    protected $digestAlgorithm = XMLSecurityDSig::SHA256;

    /**
     * Canonical algorithm URI. By default EXC_C14N.
     *
     * @var string
     *
     * @see AdapterInterface::XML_C14N
     */
    protected $canonicalMethod = XMLSecurityDSig::EXC_C14N;


    /**
     * Firma el contenido del xml y retorna el contenido firmado.
     *
     * @param string $content
     * @return string
     */
    public function signXml($content)
    {
        $doc = $this->getDocXml($content);
        $this->sign($doc);

        return $doc->saveXML();
    }

    /**
     * Verifica la firma del xml.
     *
     * @param string $content
     * @return bool
     */
    public function verifyXml($content)
    {
        $doc = $this->getDocXml($content);
        $this->getPublicKey($doc);

        return $this->verify($doc);
    }

    /**
     * Conjunto certificado en formato PEM
     * @param string $cert
     */
    public function setCertificate($cert)
    {
        $this->privateKey = $cert;
        $this->publicKey = $cert;
    }

    /**
     * @param string $filename
     * @example ./path/to/file/mycert.pem
     */
    public function setCertificateFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('Archivo de certificado no encontrado');
        }

        $this->setCertificate(file_get_contents($filename));
    }

    /**
     * @inheritdoc
     */
    public function getPublicKey(DOMDocument $doc = null)
    {
        if ($doc) {
            $this->setPublicKeyFromNode($doc);
        }

        return $this->publicKey;
    }

    /**
     * @inheritdoc
     */
    public function sign(DOMDocument $data)
    {
        if (null === $this->privateKey) {
            throw new RuntimeException(
                'Falta la clave privada. Use setPrivateKey para configurar uno.'
            );
        }

        $objKey = new XMLSecurityKey(
            $this->keyAlgorithm,
            [
                'type' => 'private',
            ]
        );
        $objKey->loadKey($this->privateKey);

        $objXMLSecDSig = $this->createXmlSecurityDSig();
        $objXMLSecDSig->setCanonicalMethod($this->canonicalMethod);
        $objXMLSecDSig->addReference($data, $this->digestAlgorithm, [self::ENVELOPED], ['force_uri' => true]);
        $objXMLSecDSig->sign($objKey, $this->getNodeSign($data));

        /* Add associated public key */
        if ($this->getPublicKey()) {
            $objXMLSecDSig->add509Cert($this->getPublicKey());
        }
    }

    /**
     * Firmar desde archivo.
     * @param string $filename
     * @return string
     */
    public function signFromFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('Archivo para firmar, no encontrado');
        }

        return $this->signXml(file_get_contents($filename));
    }

    /**
     * @inheritdoc
     */
    public function verify(DOMDocument $data)
    {
        $objKey = null;
        $objXMLSecDSig = $this->createXmlSecurityDSig();
        $objDSig = $objXMLSecDSig->locateSignature($data);
        if (!$objDSig) {
            throw new UnexpectedValueException('Elemento DOM de firma no encontrado.');
        }
        $objXMLSecDSig->canonicalizeSignedInfo();

        if (!$this->getPublicKey()) {
            // try to get the public key from the certificate
            $objKey = $objXMLSecDSig->locateKey();
            if (!$objKey) {
                throw new RuntimeException(
                    'No hay una clave privada ni una clave pública establecidas para la verificación de la firma.'
                );
            }

            XMLSecEnc::staticLocateKeyInfo($objKey, $objDSig);
            $this->publicKey = $objKey->getX509Certificate();
            $this->keyAlgorithm = $objKey->getAlgorithm();
        }

        if (!$objKey) {
            $objKey = new XMLSecurityKey(
                $this->keyAlgorithm,
                [
                    'type' => 'public',
                ]
            );
            $objKey->loadKey($this->getPublicKey());
        }

        // Check signature
        if (1 !== $objXMLSecDSig->verify($objKey)) {
            return false;
        }

        // Check references (data)
        try {
            $objXMLSecDSig->validateReference();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Crear la clase XMLSecurityDSig.
     *
     * @return XMLSecurityDSig
     */
    protected function createXmlSecurityDSig()
    {
        return new XMLSecurityDSig('');
    }

    /**
     * Intente extraer la clave pública del nodo DOM.
     *
     * Establece las propiedades publicKey y keyAlgorithm si tiene éxito.
     *
     * @see publicKey
     * @see keyAlgorithm
     *
     * @param DOMDocument $doc
     *
     * @return bool `verdadero` si se extrajo la clave pública o `falso` si no puede ser posible
     * @throws \Exception
     */
    protected function setPublicKeyFromNode(DOMDocument $doc)
    {
        // try to get the public key from the certificate
        $objXMLSecDSig = $this->createXmlSecurityDSig();
        $objDSig = $objXMLSecDSig->locateSignature($doc);
        if (!$objDSig) {
            return false;
        }

        $objKey = $objXMLSecDSig->locateKey();
        if (!$objKey) {
            return false;
        }

        XMLSecEnc::staticLocateKeyInfo($objKey, $objDSig);
        $this->publicKey = $objKey->getX509Certificate();
        $this->keyAlgorithm = $objKey->getAlgorithm();

        return true;
    }

    private function getNodeSign(DOMDocument $data)
    {
        $els = $data->getElementsByTagNameNS(
            self::EXT_NS,
            'ExtensionContent'
        );

        $nodeSign = null;
        foreach ($els as $element) {
            /** @var \DOMElement $element*/
            $val = $element->nodeValue;
            if (strlen(trim($val)) === 0) {
                $nodeSign = $element;
                break;
            }
        }

        if ($nodeSign == null) {
            $nodeSign = $data->documentElement;
        }

        return $nodeSign;
    }

    /**
     * @param string $content
     * @return \DOMDocument
     */
    private function getDocXml($content)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($content);

        return $doc;
    }
}
