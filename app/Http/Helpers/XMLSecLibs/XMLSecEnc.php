<?php

namespace App\Http\Helpers\XMLSecLibs;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use App\Http\Helpers\XMLSecLibs\Util\XPath as XPath;

class XMLSecEnc
{
    const TEMPLATE = "<xenc:EncryptedData xmlns:xenc='http://www.w3.org/2001/04/xmlenc#'>
   <xenc:CipherData>
      <xenc:CipherValue></xenc:CipherValue>
   </xenc:CipherData>
</xenc:EncryptedData>";

    const ELEMENT = 'http://www.w3.org/2001/04/xmlenc#Element';
    const CONTENT = 'http://www.w3.org/2001/04/xmlenc#Content';
    const URI = 3;
    const XMLENCNS = 'http://www.w3.org/2001/04/xmlenc#';

    /** @var null|DOMDocument */
    private $encdoc = null;

    /** @var null|DOMNode  */
    private $rawNode = null;

    /** @var null|string */
    public $type = null;

    /** @var null|DOMElement */
    public $encKey = null;

    /** @var array */
    private $references = array();

    public function __construct()
    {
        $this->_resetTemplate();
    }

    private function _resetTemplate()
    {
        $this->encdoc = new DOMDocument();
        $this->encdoc->loadXML(self::TEMPLATE);
    }

    /**
     * @param string $name
     * @param DOMNode $node
     * @param string $type
     * @throws Exception
     */
    public function addReference($name, $node, $type)
    {
        if (!$node instanceof DOMNode) {
            throw new Exception('$node no es del tipo DOMNode');
        }
        $curencdoc = $this->encdoc;
        $this->_resetTemplate();
        $encdoc = $this->encdoc;
        $this->encdoc = $curencdoc;
        $refuri = XMLSecurityDSig::generateGUID();
        $element = $encdoc->documentElement;
        $element->setAttribute("Id", $refuri);
        $this->references[$name] = array("node" => $node, "type" => $type, "encnode" => $encdoc, "refuri" => $refuri);
    }

    /**
     * @param DOMNode $node
     */
    public function setNode($node)
    {
        $this->rawNode = $node;
    }

    /**
     * Cifre el nodo seleccionado con la clave dada.
     *
     * @param XMLSecurityKey $objKey  La clave de cifrado y el algoritmo.
     * @param bool           $replace Si el nodo cifrado debe reemplazarse en el árbol original. El valor predeterminado es verdadero.
     * @throws Exception
     *
     * @return DOMElement  El elemento <xenc:EncryptedData>.
     */
    public function encryptNode($objKey, $replace = true)
    {
        $data = '';
        if (empty($this->rawNode)) {
            throw new Exception('No se ha configurado el nodo para cifrar');
        }
        if (!$objKey instanceof XMLSecurityKey) {
            throw new Exception('Key inválida');
        }
        $doc = $this->rawNode->ownerDocument;
        $xPath = new DOMXPath($this->encdoc);
        $objList = $xPath->query('/xenc:EncryptedData/xenc:CipherData/xenc:CipherValue');
        $cipherValue = $objList->item(0);
        if ($cipherValue == null) {
            throw new Exception('Error al ubicar el elemento CipherValue dentro de la plantilla');
        }
        switch ($this->type) {
            case (self::ELEMENT):
                $data = $doc->saveXML($this->rawNode);
                $this->encdoc->documentElement->setAttribute('Type', self::ELEMENT);
                break;
            case (self::CONTENT):
                $children = $this->rawNode->childNodes;
                foreach ($children as $child) {
                    $data .= $doc->saveXML($child);
                }
                $this->encdoc->documentElement->setAttribute('Type', self::CONTENT);
                break;
            default:
                throw new Exception('El tipo no es compatible actualmente');
        }

        $encMethod = $this->encdoc->documentElement->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:EncryptionMethod'));
        $encMethod->setAttribute('Algorithm', $objKey->getAlgorithm());
        $cipherValue->parentNode->parentNode->insertBefore($encMethod, $cipherValue->parentNode->parentNode->firstChild);

        $strEncrypt = base64_encode($objKey->encryptData($data));
        $value = $this->encdoc->createTextNode($strEncrypt);
        $cipherValue->appendChild($value);

        if ($replace) {
            switch ($this->type) {
                case (self::ELEMENT):
                    if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                        return $this->encdoc;
                    }
                    $importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                    $this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);
                    return $importEnc;
                case (self::CONTENT):
                    $importEnc = $this->rawNode->ownerDocument->importNode($this->encdoc->documentElement, true);
                    while ($this->rawNode->firstChild) {
                        $this->rawNode->removeChild($this->rawNode->firstChild);
                    }
                    $this->rawNode->appendChild($importEnc);
                    return $importEnc;
                default:
                    throw new Exception('Tipo no válido');
            }
        }

        return $this->encdoc->documentElement;
    }

    /**
     * @param XMLSecurityKey $objKey
     * @throws Exception
     */
    public function encryptReferences($objKey)
    {
        $curRawNode = $this->rawNode;
        $curType = $this->type;
        foreach ($this->references as $name => $reference) {
            $this->encdoc = $reference["encnode"];
            $this->rawNode = $reference["node"];
            $this->type = $reference["type"];
            try {
                $encNode = $this->encryptNode($objKey);
                $this->references[$name]["encnode"] = $encNode;
            } catch (Exception $e) {
                $this->rawNode = $curRawNode;
                $this->type = $curType;
                throw $e;
            }
        }
        $this->rawNode = $curRawNode;
        $this->type = $curType;
    }

    /**
     * Recupera el texto CipherValue de este nodo cifrado.
     *
     * @throws Exception
     * @return string|null  El texto del valor de cifrado, o nulo si no se encuentra ningún valor de cifrado.
     */
    public function getCipherValue()
    {
        if (empty($this->rawNode)) {
            throw new Exception('No se ha configurado el nodo para descifrar');
        }

        $doc = $this->rawNode->ownerDocument;
        $xPath = new DOMXPath($doc);
        $xPath->registerNamespace('xmlencr', self::XMLENCNS);
        /* Only handles embedded content right now and not a reference */
        $query = "./xmlencr:CipherData/xmlencr:CipherValue";
        $nodeset = $xPath->query($query, $this->rawNode);
        $node = $nodeset->item(0);

        if (!$node) {
            return null;
        }

        return base64_decode($node->nodeValue);
    }

    /**
     * Descifrar el nodo cifrado.
     *
     * El comportamiento de esta función depende del valor de $replace.
     * Si $replace es falso, devolveremos los datos descifrados como una cadena.
     * Si $replace es verdadero, insertaremos los elementos descifrados en el
     * documento y devolver los elementos descifrados.
     *
     * @param XMLSecurityKey $objKey La clave de descifrado que debe usarse al descifrar el nodo.
     * @param boolean $replace Si debemos reemplazar el nodo cifrado en el documento XML con los datos descifrados. El defecto es cierto.
     *
     * @return string|DOMElement  Los datos descifrados.
     * @throws Exception
     */
    public function decryptNode($objKey, $replace = true)
    {
        if (!$objKey instanceof XMLSecurityKey) {
            throw new Exception('key inválido');
        }

        $encryptedData = $this->getCipherValue();
        if ($encryptedData) {
            $decrypted = $objKey->decryptData($encryptedData);
            if ($replace) {
                switch ($this->type) {
                    case (self::ELEMENT):
                        $newdoc = new DOMDocument();
                        $newdoc->loadXML($decrypted);
                        if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                            return $newdoc;
                        }
                        $importEnc = $this->rawNode->ownerDocument->importNode($newdoc->documentElement, true);
                        $this->rawNode->parentNode->replaceChild($importEnc, $this->rawNode);
                        return $importEnc;
                    case (self::CONTENT):
                        if ($this->rawNode->nodeType == XML_DOCUMENT_NODE) {
                            $doc = $this->rawNode;
                        } else {
                            $doc = $this->rawNode->ownerDocument;
                        }
                        $newFrag = $doc->createDocumentFragment();
                        $newFrag->appendXML($decrypted);
                        $parent = $this->rawNode->parentNode;
                        $parent->replaceChild($newFrag, $this->rawNode);
                        return $parent;
                    default:
                        return $decrypted;
                }
            } else {
                return $decrypted;
            }
        } else {
            throw new Exception("No se pueden localizar los datos cifrados");
        }
    }

    /**
     * Encrypt the XMLSecurityKey
     *
     * @param XMLSecurityKey $srcKey
     * @param XMLSecurityKey $rawKey
     * @param bool $append
     * @throws Exception
     */
    public function encryptKey($srcKey, $rawKey, $append = true)
    {
        if ((!$srcKey instanceof XMLSecurityKey) || (!$rawKey instanceof XMLSecurityKey)) {
            throw new Exception('Invalid Key');
        }
        $strEncKey = base64_encode($srcKey->encryptData($rawKey->key));
        $root = $this->encdoc->documentElement;
        $encKey = $this->encdoc->createElementNS(self::XMLENCNS, 'xenc:EncryptedKey');
        if ($append) {
            $keyInfo = $root->insertBefore($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'), $root->firstChild);
            $keyInfo->appendChild($encKey);
        } else {
            $this->encKey = $encKey;
        }
        $encMethod = $encKey->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:EncryptionMethod'));
        $encMethod->setAttribute('Algorithm', $srcKey->getAlgorith());
        if (!empty($srcKey->name)) {
            $keyInfo = $encKey->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyInfo'));
            $keyInfo->appendChild($this->encdoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'dsig:KeyName', $srcKey->name));
        }
        $cipherData = $encKey->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:CipherData'));
        $cipherData->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:CipherValue', $strEncKey));
        if (is_array($this->references) && count($this->references) > 0) {
            $refList = $encKey->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:ReferenceList'));
            foreach ($this->references as $name => $reference) {
                $refuri = $reference["refuri"];
                $dataRef = $refList->appendChild($this->encdoc->createElementNS(self::XMLENCNS, 'xenc:DataReference'));
                $dataRef->setAttribute("URI", '#' . $refuri);
            }
        }
        return;
    }

    /**
     * @param XMLSecurityKey $encKey
     * @return DOMElement|string
     * @throws Exception
     */
    public function decryptKey($encKey)
    {
        if (!$encKey->isEncrypted) {
            throw new Exception("La clave no está cifrada");
        }
        if (empty($encKey->key)) {
            throw new Exception("A la clave le faltan datos para realizar el descifrado");
        }
        return $this->decryptNode($encKey, false);
    }

    /**
     * @param DOMDocument $element
     * @return DOMNode|null
     */
    public function locateEncryptedData($element)
    {
        if ($element instanceof DOMDocument) {
            $doc = $element;
        } else {
            $doc = $element->ownerDocument;
        }
        if ($doc) {
            $xpath = new DOMXPath($doc);
            $query = "//*[local-name()='EncryptedData' and namespace-uri()='" . self::XMLENCNS . "']";
            $nodeset = $xpath->query($query);
            return $nodeset->item(0);
        }
        return null;
    }

    /**
     * Returns the key from the DOM
     * @param null|DOMNode $node
     * @return null|XMLSecurityKey
     */
    public function locateKey($node = null)
    {
        if (empty($node)) {
            $node = $this->rawNode;
        }
        if (!$node instanceof DOMNode) {
            return null;
        }
        if ($doc = $node->ownerDocument) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('xmlsecenc', self::XMLENCNS);
            $query = ".//xmlsecenc:EncryptionMethod";
            $nodeset = $xpath->query($query, $node);
            if ($encmeth = $nodeset->item(0)) {
                $attrAlgorithm = $encmeth->getAttribute("Algorithm");
                try {
                    $objKey = new XMLSecurityKey($attrAlgorithm, array('type' => 'private'));
                } catch (Exception $e) {
                    return null;
                }
                return $objKey;
            }
        }
        return null;
    }

    /**
     * @param null|XMLSecurityKey $objBaseKey
     * @param null|DOMNode $node
     * @return null|XMLSecurityKey
     * @throws Exception
     */
    public static function staticLocateKeyInfo($objBaseKey = null, $node = null)
    {
        if (empty($node) || (!$node instanceof DOMNode)) {
            return null;
        }
        $doc = $node->ownerDocument;
        if (!$doc) {
            return null;
        }

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('xmlsecenc', self::XMLENCNS);
        $xpath->registerNamespace('xmlsecdsig', XMLSecurityDSig::XMLDSIGNS);
        $query = "./xmlsecdsig:KeyInfo";
        $nodeset = $xpath->query($query, $node);
        $encmeth = $nodeset->item(0);
        if (!$encmeth) {
            /* No KeyInfo in EncryptedData / EncryptedKey. */
            return $objBaseKey;
        }

        foreach ($encmeth->childNodes as $child) {
            switch ($child->localName) {
                case 'KeyName':
                    if (!empty($objBaseKey)) {
                        $objBaseKey->name = $child->nodeValue;
                    }
                    break;
                case 'KeyValue':
                    foreach ($child->childNodes as $keyval) {
                        switch ($keyval->localName) {
                            case 'DSAKeyValue':
                                throw new Exception("DSAKeyValue actualmente no es compatible");
                            case 'RSAKeyValue':
                                $modulus = null;
                                $exponent = null;
                                if ($modulusNode = $keyval->getElementsByTagName('Modulus')->item(0)) {
                                    $modulus = base64_decode($modulusNode->nodeValue);
                                }
                                if ($exponentNode = $keyval->getElementsByTagName('Exponent')->item(0)) {
                                    $exponent = base64_decode($exponentNode->nodeValue);
                                }
                                if (empty($modulus) || empty($exponent)) {
                                    throw new Exception("módulo o exponente faltante");
                                }
                                $publicKey = XMLSecurityKey::convertRSA($modulus, $exponent);
                                $objBaseKey->loadKey($publicKey);
                                break;
                        }
                    }
                    break;
                case 'RetrievalMethod':
                    $type = $child->getAttribute('Type');
                    if ($type !== 'http://www.w3.org/2001/04/xmlenc#EncryptedKey') {
                        /* Unsupported key type. */
                        break;
                    }
                    $uri = $child->getAttribute('URI');
                    if ($uri[0] !== '#') {
                        /* URI not a reference - unsupported. */
                        break;
                    }
                    $id = substr($uri, 1);

                    $query = '//xmlsecenc:EncryptedKey[@Id="' . XPath::filterAttrValue($id, XPath::DOUBLE_QUOTE) . '"]';
                    $keyElement = $xpath->query($query)->item(0);
                    if (!$keyElement) {
                        throw new Exception("No se puede localizar EncryptedKey con @Id='$id'.");
                    }

                    return XMLSecurityKey::fromEncryptedKeyElement($keyElement);
                case 'EncryptedKey':
                    return XMLSecurityKey::fromEncryptedKeyElement($child);
                case 'X509Data':
                    if ($x509certNodes = $child->getElementsByTagName('X509Certificate')) {
                        if ($x509certNodes->length > 0) {
                            $x509cert = $x509certNodes->item(0)->textContent;
                            $x509cert = str_replace(array("\r", "\n", " "), "", $x509cert);
                            $x509cert = "-----BEGIN CERTIFICATE-----\n" . chunk_split($x509cert, 64, "\n") . "-----END CERTIFICATE-----\n";
                            $objBaseKey->loadKey($x509cert, false, true);
                        }
                    }
                    break;
            }
        }
        return $objBaseKey;
    }

    /**
     * @param null|XMLSecurityKey $objBaseKey
     * @param null|DOMNode $node
     * @return null|XMLSecurityKey
     * @throws Exception
     */
    public function locateKeyInfo($objBaseKey = null, $node = null)
    {
        if (empty($node)) {
            $node = $this->rawNode;
        }
        return self::staticLocateKeyInfo($objBaseKey, $node);
    }
}
