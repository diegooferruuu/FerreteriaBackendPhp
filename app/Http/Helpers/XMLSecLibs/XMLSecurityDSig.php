<?php

namespace App\Http\Helpers\XMLSecLibs;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Exception;
use App\Http\Helpers\XMLSecLibs\Util\XPath;

class XMLSecurityDSig
{
    const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';
    const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
    const SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
    const SHA384 = 'http://www.w3.org/2001/04/xmldsig-more#sha384';
    const SHA512 = 'http://www.w3.org/2001/04/xmlenc#sha512';
    const RIPEMD160 = 'http://www.w3.org/2001/04/xmlenc#ripemd160';

    const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    const C14N_COMMENTS = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments';
    const EXC_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    const EXC_C14N_COMMENTS = 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments';

    const template = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ds:SignedInfo>
    <ds:SignatureMethod />
  </ds:SignedInfo>
</ds:Signature>';

    const BASE_TEMPLATE = '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><SignatureMethod /></SignedInfo></Signature>';

    /** @var DOMElement|null */
    public $sigNode = null;

    /** @var array */
    public $idKeys = array();

    /** @var array */
    public $idNS = array();

    /** @var string|null */
    private $signedInfo = null;

    /** @var DomXPath|null */
    private $xPathCtx = null;

    /** @var string|null */
    private $canonicalMethod = null;

    /** @var string */
    private $prefix = '';

    /** @var string */
    private $searchpfx = 'secdsig';

    /**
     * Esta variable contiene una matriz asociativa de nodos validados.
     * @var array|null
     */
    private $validatedNodes = null;

    /**
     * @param string $prefix
     */
    public function __construct($prefix = 'ds')
    {
        $template = self::BASE_TEMPLATE;
        if (!empty($prefix)) {
            $this->prefix = $prefix . ':';
            $search = array("<S", "</S", "xmlns=");
            $replace = array("<$prefix:S", "</$prefix:S", "xmlns:$prefix=");
            $template = str_replace($search, $replace, $template);
        }
        $sigdoc = new DOMDocument();
        $sigdoc->loadXML($template);
        $this->sigNode = $sigdoc->documentElement;
    }

    /**
     * Restablecer el XPathObj a nulo
     */
    private function resetXPathObj()
    {
        $this->xPathCtx = null;
    }

    /**
     * Devuelve XPathObj o nulo si xPathCtx está configurado y sigNode está vacío.
     *
     * @return DOMXPath|null
     */
    private function getXPathObj()
    {
        if (empty($this->xPathCtx) && !empty($this->sigNode)) {
            $xpath = new DOMXPath($this->sigNode->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $this->xPathCtx = $xpath;
        }
        return $this->xPathCtx;
    }

    /**
     * generar guid
     *
     * @param string $prefix Prefijo a usar para guid. por defecto a pfx
     *
     * @return string El GUID generado
     */
    public static function generateGUID($prefix = 'pfx')
    {
        $uuid = md5(uniqid(mt_rand(), true));
        $guid = $prefix . substr($uuid, 0, 8) . "-" .
            substr($uuid, 8, 4) . "-" .
            substr($uuid, 12, 4) . "-" .
            substr($uuid, 16, 4) . "-" .
            substr($uuid, 20, 12);
        return $guid;
    }

    /**
     * Generate guid
     *
     * @param string $prefix Prefix to use for guid. defaults to pfx
     *
     * @return string The generated guid
     *
     * @deprecated Method deprecated in Release 1.4.1
     */
    public static function generate_GUID($prefix = 'pfx')
    {
        return self::generateGUID($prefix);
    }

    /**
     * @param DOMDocument $objDoc
     * @param int $pos
     * @return DOMNode|null
     */
    public function locateSignature($objDoc, $pos = 0)
    {
        $doc = $objDoc instanceof DOMDocument ? $objDoc : $objDoc->ownerDocument;

        if ($doc) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $query = ".//secdsig:Signature";
            $nodeset = $xpath->query($query, $objDoc);
            $this->sigNode = $nodeset->item($pos);
            return $this->sigNode;
        }

        return null;
    }

    /**
     * @param string $name
     * @param null|string $value
     * @return DOMElement
     */
    public function createNewSignNode($name, $value = null)
    {
        $doc = $this->sigNode->ownerDocument;
        if (!is_null($value)) {
            $node = $doc->createElementNS(self::XMLDSIGNS, $this->prefix . $name, $value);
        } else {
            $node = $doc->createElementNS(self::XMLDSIGNS, $this->prefix . $name);
        }
        return $node;
    }

    /**
     * @param string $method
     * @throws Exception
     */
    public function setCanonicalMethod($method)
    {
        switch ($method) {
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
            case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                $this->canonicalMethod = $method;
                break;
            default:
                throw new Exception('Método canónico no válido');
        }
        if ($xpath = $this->getXPathObj()) {
            $query = './' . $this->searchpfx . ':SignedInfo';
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sinfo = $nodeset->item(0)) {
                $query = './' . $this->searchpfx . 'CanonicalizationMethod';
                $nodeset = $xpath->query($query, $sinfo);
                if (!($canonNode = $nodeset->item(0))) {
                    $canonNode = $this->createNewSignNode('CanonicalizationMethod');
                    $sinfo->insertBefore($canonNode, $sinfo->firstChild);
                }
                $canonNode->setAttribute('Algorithm', $this->canonicalMethod);
            }
        }
    }

    /**
     * @param DOMNode $node
     * @param string $canonicalmethod
     * @param null|array $arXPath
     * @param null|array $prefixList
     * @return string
     */
    private function canonicalizeData($node, $canonicalmethod, $arXPath = null, $prefixList = null)
    {
        $exclusive = false;
        $withComments = false;
        switch ($canonicalmethod) {
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                $exclusive = false;
                $withComments = false;
                break;
            case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                $withComments = true;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                $exclusive = true;
                break;
            case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':
                $exclusive = true;
                $withComments = true;
                break;
        }

        if (is_null($arXPath) && ($node instanceof DOMNode) && ($node->ownerDocument !== null) && $node->isSameNode($node->ownerDocument->documentElement)) {
            /* Check for any PI or comments as they would have been excluded */
            $element = $node;
            while ($refnode = $element->previousSibling) {
                if ($refnode->nodeType == XML_PI_NODE || (($refnode->nodeType == XML_COMMENT_NODE) && $withComments)) {
                    break;
                }
                $element = $refnode;
            }
            if ($refnode == null) {
                $node = $node->ownerDocument;
            }
        }

        return $node->C14N($exclusive, $withComments, $arXPath, $prefixList);
    }

    /**
     * @return null|string
     */
    public function canonicalizeSignedInfo()
    {

        $doc = $this->sigNode->ownerDocument;
        $canonicalmethod = null;
        if ($doc) {
            $xpath = $this->getXPathObj();
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($signInfoNode = $nodeset->item(0)) {
                $query = "./secdsig:CanonicalizationMethod";
                $nodeset = $xpath->query($query, $signInfoNode);
                if ($canonNode = $nodeset->item(0)) {
                    $canonicalmethod = $canonNode->getAttribute('Algorithm');
                }
                $this->signedInfo = $this->canonicalizeData($signInfoNode, $canonicalmethod);
                return $this->signedInfo;
            }
        }
        return null;
    }

    /**
     * @param string $digestAlgorithm
     * @param string $data
     * @param bool $encode
     * @return string
     * @throws Exception
     */
    public function calculateDigest($digestAlgorithm, $data, $encode = true)
    {
        switch ($digestAlgorithm) {
            case self::SHA1:
                $alg = 'sha1';
                break;
            case self::SHA256:
                $alg = 'sha256';
                break;
            case self::SHA384:
                $alg = 'sha384';
                break;
            case self::SHA512:
                $alg = 'sha512';
                break;
            case self::RIPEMD160:
                $alg = 'ripemd160';
                break;
            default:
                throw new Exception("Cannot validate digest: Unsupported Algorithm <$digestAlgorithm>");
        }

        $digest = hash($alg, $data, true);
        if ($encode) {
            $digest = base64_encode($digest);
        }
        return $digest;
    }

    /**
     * @param $refNode
     * @param string $data
     * @return bool
     * @throws Exception
     */
    public function validateDigest($refNode, $data)
    {
        $xpath = new DOMXPath($refNode->ownerDocument);
        $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        $query = 'string(./secdsig:DigestMethod/@Algorithm)';
        $digestAlgorithm = $xpath->evaluate($query, $refNode);
        $digValue = $this->calculateDigest($digestAlgorithm, $data, false);
        $query = 'string(./secdsig:DigestValue)';
        $digestValue = $xpath->evaluate($query, $refNode);
        return ($digValue === base64_decode($digestValue));
    }

    /**
     * @param $refNode
     * @param DOMNode $objData
     * @param bool $includeCommentNodes
     * @return string
     */
    public function processTransforms($refNode, $objData, $includeCommentNodes = true)
    {
        $data = $objData;
        $xpath = new DOMXPath($refNode->ownerDocument);
        $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        $query = './secdsig:Transforms/secdsig:Transform';
        $nodelist = $xpath->query($query, $refNode);
        $canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
        $arXPath = null;
        $prefixList = null;
        foreach ($nodelist as $transform) {
            $algorithm = $transform->getAttribute("Algorithm");
            switch ($algorithm) {
                case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                case 'http://www.w3.org/2001/10/xml-exc-c14n#WithComments':

                    if (!$includeCommentNodes) {
                        /* We remove comment nodes by forcing it to use a canonicalization
                         * without comments.
                         */
                        $canonicalMethod = 'http://www.w3.org/2001/10/xml-exc-c14n#';
                    } else {
                        $canonicalMethod = $algorithm;
                    }

                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'InclusiveNamespaces') {
                            if ($pfx = $node->getAttribute('PrefixList')) {
                                $arpfx = array();
                                $pfxlist = explode(" ", $pfx);
                                foreach ($pfxlist as $pfx) {
                                    $val = trim($pfx);
                                    if (!empty($val)) {
                                        $arpfx[] = $val;
                                    }
                                }
                                if (count($arpfx) > 0) {
                                    $prefixList = $arpfx;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
                    break;
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315':
                case 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments':
                    if (!$includeCommentNodes) {
                        /* We remove comment nodes by forcing it to use a canonicalization
                         * without comments.
                         */
                        $canonicalMethod = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
                    } else {
                        $canonicalMethod = $algorithm;
                    }

                    break;
                case 'http://www.w3.org/TR/1999/REC-xpath-19991116':
                    $node = $transform->firstChild;
                    while ($node) {
                        if ($node->localName == 'XPath') {
                            $arXPath = array();
                            $arXPath['query'] = '(.//. | .//@* | .//namespace::*)[' . $node->nodeValue . ']';
                            $arXPath['namespaces'] = array();
                            $nslist = $xpath->query('./namespace::*', $node);
                            foreach ($nslist as $nsnode) {
                                if ($nsnode->localName != "xml") {
                                    $arXPath['namespaces'][$nsnode->localName] = $nsnode->nodeValue;
                                }
                            }
                            break;
                        }
                        $node = $node->nextSibling;
                    }
                    break;
            }
        }
        if ($data instanceof DOMNode) {
            $data = $this->canonicalizeData($objData, $canonicalMethod, $arXPath, $prefixList);
        }
        return $data;
    }

    /**
     * @param DOMElement $refNode
     * @return bool
     * @throws Exception
     */
    public function processRefNode($refNode)
    {
        $dataObject = null;

        /*
         * Depending on the URI, we may not want to include comments in the result
         * See: http://www.w3.org/TR/xmldsig-core/#sec-ReferenceProcessingModel
         */
        $includeCommentNodes = true;

        if ($uri = $refNode->getAttribute("URI")) {
            $arUrl = parse_url($uri);
            if (empty($arUrl['path'])) {
                if ($identifier = $arUrl['fragment']) {

                    /* This reference identifies a node with the given id by using
                     * a URI on the form "#identifier". This should not include comments.
                     */
                    $includeCommentNodes = false;

                    $xPath = new DOMXPath($refNode->ownerDocument);
                    if ($this->idNS && is_array($this->idNS)) {
                        foreach ($this->idNS as $nspf => $ns) {
                            $xPath->registerNamespace($nspf, $ns);
                        }
                    }
                    $iDlist = '@Id="' . XPath::filterAttrValue($identifier, XPath::DOUBLE_QUOTE) . '"';
                    if (is_array($this->idKeys)) {
                        foreach ($this->idKeys as $idKey) {
                            $iDlist .= ' or @' . XPath::filterAttrName($idKey) . '="' .
                                XPath::filterAttrValue($identifier, XPath::DOUBLE_QUOTE) . '"';
                        }
                    }
                    $query = '//*[' . $iDlist . ']';
                    $dataObject = $xPath->query($query)->item(0);
                } else {
                    $dataObject = $refNode->ownerDocument;
                }
            }
        } else {
            /* This reference identifies the root node with an empty URI. This should
             * not include comments.
             */
            $includeCommentNodes = false;

            $dataObject = $refNode->ownerDocument;
        }
        $data = $this->processTransforms($refNode, $dataObject, $includeCommentNodes);
        if (!$this->validateDigest($refNode, $data)) {
            return false;
        }

        if ($dataObject instanceof DOMNode) {
            /* Add this node to the list of validated nodes. */
            if (!empty($identifier)) {
                $this->validatedNodes[$identifier] = $dataObject;
            } else {
                $this->validatedNodes[] = $dataObject;
            }
        }

        return true;
    }

    /**
     * @param DOMElement $refNode
     * @return null
     */
    public function getRefNodeID($refNode)
    {
        if ($uri = $refNode->getAttribute("URI")) {
            $arUrl = parse_url($uri);
            if (empty($arUrl['path'])) {
                if ($identifier = $arUrl['fragment']) {
                    return $identifier;
                }
            }
        }
        return null;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRefIDs()
    {
        $refids = array();

        $xpath = $this->getXPathObj();
        $query = "./secdsig:SignedInfo/secdsig:Reference";
        $nodeset = $xpath->query($query, $this->sigNode);
        if ($nodeset->length == 0) {
            throw new Exception("Reference nodes not found");
        }
        foreach ($nodeset as $refNode) {
            $refids[] = $this->getRefNodeID($refNode);
        }
        return $refids;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function validateReference()
    {
        $docElem = $this->sigNode->ownerDocument->documentElement;
        if (!$docElem->isSameNode($this->sigNode)) {
            if ($this->sigNode->parentNode != null) {
                $this->sigNode->parentNode->removeChild($this->sigNode);
            }
        }
        $xpath = $this->getXPathObj();
        $query = "./secdsig:SignedInfo/secdsig:Reference";
        $nodeset = $xpath->query($query, $this->sigNode);
        if ($nodeset->length == 0) {
            throw new Exception("Nodos de referencia no encontrados");
        }

        /* Initialize/reset the list of validated nodes. */
        $this->validatedNodes = array();

        foreach ($nodeset as $refNode) {
            if (!$this->processRefNode($refNode)) {
                /* Clear the list of validated nodes. */
                $this->validatedNodes = null;
                throw new Exception("Falló la validación de la referencia");
            }
        }
        return true;
    }

    /**
     * @param DOMNode $sinfoNode
     * @param DOMDocument $node
     * @param string $algorithm
     * @param null|array $arTransforms
     * @param null|array $options
     * @throws Exception
     */
    private function addRefInternal($sinfoNode, $node, $algorithm, $arTransforms = null, $options = null)
    {
        $prefix = null;
        $prefix_ns = null;
        $id_name = 'Id';
        $overwrite_id  = true;
        $force_uri = false;

        if (is_array($options)) {
            $prefix = empty($options['prefix']) ? null : $options['prefix'];
            $prefix_ns = empty($options['prefix_ns']) ? null : $options['prefix_ns'];
            $id_name = empty($options['id_name']) ? 'Id' : $options['id_name'];
            $overwrite_id = !isset($options['overwrite']) ? true : (bool) $options['overwrite'];
            $force_uri = !isset($options['force_uri']) ? false : (bool) $options['force_uri'];
        }

        $attname = $id_name;
        if (!empty($prefix)) {
            $attname = $prefix . ':' . $attname;
        }

        $refNode = $this->createNewSignNode('Reference');
        $sinfoNode->appendChild($refNode);

        if (!$node instanceof DOMDocument) {
            $uri = null;
            if (!$overwrite_id) {
                $uri = $prefix_ns ? $node->getAttributeNS($prefix_ns, $id_name) : $node->getAttribute($id_name);
            }
            if (empty($uri)) {
                $uri = self::generateGUID();
                $node->setAttributeNS($prefix_ns, $attname, $uri);
            }
            $refNode->setAttribute("URI", '#' . $uri);
        } elseif ($force_uri) {
            $refNode->setAttribute("URI", '');
        }

        $transNodes = $this->createNewSignNode('Transforms');
        $refNode->appendChild($transNodes);

        if (is_array($arTransforms)) {
            foreach ($arTransforms as $transform) {
                $transNode = $this->createNewSignNode('Transform');
                $transNodes->appendChild($transNode);
                if (
                    is_array($transform) &&
                    (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116'])) &&
                    (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']))
                ) {
                    $transNode->setAttribute('Algorithm', 'http://www.w3.org/TR/1999/REC-xpath-19991116');
                    $XPathNode = $this->createNewSignNode('XPath', $transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['query']);
                    $transNode->appendChild($XPathNode);
                    if (!empty($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'])) {
                        foreach ($transform['http://www.w3.org/TR/1999/REC-xpath-19991116']['namespaces'] as $prefix => $namespace) {
                            $XPathNode->setAttributeNS("http://www.w3.org/2000/xmlns/", "xmlns:$prefix", $namespace);
                        }
                    }
                } else {
                    $transNode->setAttribute('Algorithm', $transform);
                }
            }
        } elseif (!empty($this->canonicalMethod)) {
            $transNode = $this->createNewSignNode('Transform');
            $transNodes->appendChild($transNode);
            $transNode->setAttribute('Algorithm', $this->canonicalMethod);
        }

        $canonicalData = $this->processTransforms($refNode, $node);
        $digValue = $this->calculateDigest($algorithm, $canonicalData);

        $digestMethod = $this->createNewSignNode('DigestMethod');
        $refNode->appendChild($digestMethod);
        $digestMethod->setAttribute('Algorithm', $algorithm);

        $digestValue = $this->createNewSignNode('DigestValue', $digValue);
        $refNode->appendChild($digestValue);
    }

    /**
     * @param DOMDocument $node
     * @param string $algorithm
     * @param null|array $arTransforms
     * @param null|array $options
     * @throws Exception
     */
    public function addReference($node, $algorithm, $arTransforms = null, $options = null)
    {
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                $this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
            }
        }
    }

    /**
     * @param array $arNodes
     * @param string $algorithm
     * @param null|array $arTransforms
     * @param null|array $options
     * @throws Exception
     */
    public function addReferenceList($arNodes, $algorithm, $arTransforms = null, $options = null)
    {
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                foreach ($arNodes as $node) {
                    $this->addRefInternal($sInfo, $node, $algorithm, $arTransforms, $options);
                }
            }
        }
    }

    /**
     * @param DOMElement|string $data
     * @param null|string $mimetype
     * @param null|string $encoding
     * @return DOMElement
     */
    public function addObject($data, $mimetype = null, $encoding = null)
    {
        $objNode = $this->createNewSignNode('Object');
        $this->sigNode->appendChild($objNode);
        if (!empty($mimetype)) {
            $objNode->setAttribute('MimeType', $mimetype);
        }
        if (!empty($encoding)) {
            $objNode->setAttribute('Encoding', $encoding);
        }

        if ($data instanceof DOMElement) {
            $newData = $this->sigNode->ownerDocument->importNode($data, true);
        } else {
            $newData = $this->sigNode->ownerDocument->createTextNode($data);
        }
        $objNode->appendChild($newData);

        return $objNode;
    }

    /**
     * @param null|DOMNode $node
     * @return null|XMLSecurityKey
     */
    public function locateKey($node = null)
    {
        if (empty($node)) {
            $node = $this->sigNode;
        }
        if (!$node instanceof DOMNode) {
            return null;
        }
        if ($doc = $node->ownerDocument) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
            $query = "string(./secdsig:SignedInfo/secdsig:SignatureMethod/@Algorithm)";
            $algorithm = $xpath->evaluate($query, $node);
            if ($algorithm) {
                try {
                    $objKey = new XMLSecurityKey($algorithm, array('type' => 'public'));
                } catch (Exception $e) {
                    return null;
                }
                return $objKey;
            }
        }
        return null;
    }

    /**
     * Devoluciones:
     * Bool al verificar HMAC_SHA1;
     * Int de otro modo, con los siguientes significados:
     * 1 en verificación de firma exitosa,
     * 0 cuando falla la verificación de la firma,
     * -1 si ocurrió un error durante el procesamiento.
     *
     * NOTA: tenga mucho cuidado al verificar el valor de retorno de int, porque en
     * PHP, -1 se convertirá en True cuando esté en contexto booleano. Siempre revise el
     * valor devuelto de forma estrictamente escrita, p. "$obj->verificar(...) === 1".
     *
     * @param XMLSecurityKey $objKey
     * @return bool|int
     * @throws Exception
     */
    public function verify($objKey)
    {
        $doc = $this->sigNode->ownerDocument;
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        $query = "string(./secdsig:SignatureValue)";
        $sigValue = $xpath->evaluate($query, $this->sigNode);
        if (empty($sigValue)) {
            throw new Exception("No se puede localizar SignatureValue");
        }
        return $objKey->verifySignature($this->signedInfo, base64_decode($sigValue));
    }

    /**
     * @param XMLSecurityKey $objKey
     * @param string $data
     * @return mixed|string
     * @throws Exception
     */
    public function signData($objKey, $data)
    {
        return $objKey->signData($data);
    }

    /**
     * @param XMLSecurityKey $objKey
     * @param null|DOMNode $appendToNode
     * @throws Exception
     */
    public function sign($objKey, $appendToNode = null)
    {
        // If we have a parent node append it now so C14N properly works
        if ($appendToNode != null) {
            $this->resetXPathObj();
            $this->appendSignature($appendToNode);
            $this->sigNode = $appendToNode->lastChild;
        }
        if ($xpath = $this->getXPathObj()) {
            $query = "./secdsig:SignedInfo";
            $nodeset = $xpath->query($query, $this->sigNode);
            if ($sInfo = $nodeset->item(0)) {
                $query = "./secdsig:SignatureMethod";
                $nodeset = $xpath->query($query, $sInfo);
                $sMethod = $nodeset->item(0);
                $sMethod->setAttribute('Algorithm', $objKey->type);
                $data = $this->canonicalizeData($sInfo, $this->canonicalMethod);
                $sigValue = base64_encode($this->signData($objKey, $data));
                $sigValueNode = $this->createNewSignNode('SignatureValue', $sigValue);
                if ($infoSibling = $sInfo->nextSibling) {
                    $infoSibling->parentNode->insertBefore($sigValueNode, $infoSibling);
                } else {
                    $this->sigNode->appendChild($sigValueNode);
                }
            }
        }
    }

    public function appendCert()
    {
    }


    /**
     * Esta función inserta el elemento de firma.
     *
     * El elemento de firma se agregará al elemento, a menos que se especifique $beforeNode. Si $antes del nodo
     * se especifica, el elemento de la firma se insertará como el último elemento antes de $beforeNode.
     *
     * @param DOMNode $node       El nodo en el que se debe insertar el elemento de firma.
     * @param DOMNode $beforeNode El nodo antes del cual se debe ubicar el elemento de firma.
     *
     * @return DOMNode El nodo del elemento de firma
     */
    public function insertSignature($node, $beforeNode = null)
    {

        $document = $node->ownerDocument;
        $signatureElement = $document->importNode($this->sigNode, true);

        if ($beforeNode == null) {
            return $node->insertBefore($signatureElement);
        } else {
            return $node->insertBefore($signatureElement, $beforeNode);
        }
    }

    /**
     * @param DOMNode $parentNode
     * @param bool $insertBefore
     * @return DOMNode
     */
    public function appendSignature($parentNode, $insertBefore = false)
    {
        $beforeNode = $insertBefore ? $parentNode->firstChild : null;
        return $this->insertSignature($parentNode, $beforeNode);
    }

    /**
     * @param string $cert
     * @param bool $isPEMFormat
     * @return string
     */
    public static function get509XCert($cert, $isPEMFormat = true)
    {
        $certs = self::staticGet509XCerts($cert, $isPEMFormat);
        if (!empty($certs)) {
            return $certs[0];
        }
        return '';
    }

    /**
     * @param string $certs
     * @param bool $isPEMFormat
     * @return array
     */
    public static function staticGet509XCerts($certs, $isPEMFormat = true)
    {
        if ($isPEMFormat) {
            $data = '';
            $certlist = array();
            $arCert = explode("\n", $certs);
            $inData = false;
            foreach ($arCert as $curData) {
                if (!$inData) {
                    if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) == 0) {
                        $inData = true;
                    }
                } else {
                    if (strncmp($curData, '-----END CERTIFICATE', 20) == 0) {
                        $inData = false;
                        $certlist[] = $data;
                        $data = '';
                        continue;
                    }
                    $data .= trim($curData);
                }
            }
            return $certlist;
        } else {
            return array($certs);
        }
    }

    /**
     * @param DOMElement $parentRef
     * @param string $cert
     * @param bool $isPEMFormat
     * @param bool $isURL
     * @param null|DOMXPath $xpath
     * @param null|array $options
     * @throws Exception
     */
    public static function staticAdd509Cert($parentRef, $cert, $isPEMFormat = true, $isURL = false, $xpath = null, $options = null)
    {
        if ($isURL) {
            $cert = file_get_contents($cert);
        }
        if (!$parentRef instanceof DOMElement) {
            throw new Exception('Parámetro de nodo principal no válido');
        }
        $baseDoc = $parentRef->ownerDocument;

        if (empty($xpath)) {
            $xpath = new DOMXPath($parentRef->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        }

        $query = "./secdsig:KeyInfo";
        $nodeset = $xpath->query($query, $parentRef);
        $keyInfo = $nodeset->item(0);
        $dsig_pfx = '';
        if (!$keyInfo) {
            $pfx = $parentRef->lookupPrefix(self::XMLDSIGNS);
            if (!empty($pfx)) {
                $dsig_pfx = $pfx . ":";
            }
            $inserted = false;
            $keyInfo = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'KeyInfo');

            $query = "./secdsig:Object";
            $nodeset = $xpath->query($query, $parentRef);
            if ($sObject = $nodeset->item(0)) {
                $sObject->parentNode->insertBefore($keyInfo, $sObject);
                $inserted = true;
            }

            if (!$inserted) {
                $parentRef->appendChild($keyInfo);
            }
        } else {
            $pfx = $keyInfo->lookupPrefix(self::XMLDSIGNS);
            if (!empty($pfx)) {
                $dsig_pfx = $pfx . ":";
            }
        }

        // Add all certs if there are more than one
        $certs = self::staticGet509XCerts($cert, $isPEMFormat);

        // Attach X509 data node
        $x509DataNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'X509Data');
        $keyInfo->appendChild($x509DataNode);

        $issuerSerial = false;
        $subjectName = false;
        if (is_array($options)) {
            if (!empty($options['issuerSerial'])) {
                $issuerSerial = true;
            }
            if (!empty($options['subjectName'])) {
                $subjectName = true;
            }
        }

        // Attach all certificate nodes and any additional data
        foreach ($certs as $X509Cert) {
            if ($issuerSerial || $subjectName) {
                if ($certData = openssl_x509_parse("-----BEGIN CERTIFICATE-----\n" . chunk_split($X509Cert, 64, "\n") . "-----END CERTIFICATE-----\n")) {
                    if ($subjectName && !empty($certData['subject'])) {
                        if (is_array($certData['subject'])) {
                            $parts = array();
                            foreach ($certData['subject'] as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $valueElement) {
                                        array_unshift($parts, "$key=$valueElement");
                                    }
                                } else {
                                    array_unshift($parts, "$key=$value");
                                }
                            }
                            $subjectNameValue = implode(',', $parts);
                        } else {
                            $subjectNameValue = $certData['issuer'];
                        }
                        $x509SubjectNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'X509SubjectName', $subjectNameValue);
                        $x509DataNode->appendChild($x509SubjectNode);
                    }
                    if ($issuerSerial && !empty($certData['issuer']) && !empty($certData['serialNumber'])) {
                        if (is_array($certData['issuer'])) {
                            $parts = array();
                            foreach ($certData['issuer'] as $key => $value) {
                                array_unshift($parts, "$key=$value");
                            }
                            $issuerName = implode(',', $parts);
                        } else {
                            $issuerName = $certData['issuer'];
                        }

                        $x509IssuerNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'X509IssuerSerial');
                        $x509DataNode->appendChild($x509IssuerNode);

                        $x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'X509IssuerName', $issuerName);
                        $x509IssuerNode->appendChild($x509Node);
                        $x509Node = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'X509SerialNumber', $certData['serialNumber']);
                        $x509IssuerNode->appendChild($x509Node);
                    }
                }
            }
            $x509CertNode = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'X509Certificate', $X509Cert);
            $x509DataNode->appendChild($x509CertNode);
        }
    }

    /**
     * @param string $cert
     * @param bool $isPEMFormat
     * @param bool $isURL
     * @param null|array $options
     * @throws Exception
     */
    public function add509Cert($cert, $isPEMFormat = true, $isURL = false, $options = null)
    {
        if ($xpath = $this->getXPathObj()) {
            self::staticAdd509Cert($this->sigNode, $cert, $isPEMFormat, $isURL, $xpath, $options);
        }
    }

    /**
     * Esta función agrega un nodo a KeyInfo.
     *
     * El elemento KeyInfo se creará si no existe uno en el documento.
     *
     * @param DOMNode $node El nodo que se agregará a KeyInfo
     *
     * @return DOMNode El nodo del elemento KeyInfo
     */
    public function appendToKeyInfo($node)
    {
        $parentRef = $this->sigNode;
        $baseDoc = $parentRef->ownerDocument;

        $xpath = $this->getXPathObj();
        if (empty($xpath)) {
            $xpath = new DOMXPath($parentRef->ownerDocument);
            $xpath->registerNamespace('secdsig', self::XMLDSIGNS);
        }

        $query = "./secdsig:KeyInfo";
        $nodeset = $xpath->query($query, $parentRef);
        $keyInfo = $nodeset->item(0);
        if (!$keyInfo) {
            $dsig_pfx = '';
            $pfx = $parentRef->lookupPrefix(self::XMLDSIGNS);
            if (!empty($pfx)) {
                $dsig_pfx = $pfx . ":";
            }
            $inserted = false;
            $keyInfo = $baseDoc->createElementNS(self::XMLDSIGNS, $dsig_pfx . 'KeyInfo');

            $query = "./secdsig:Object";
            $nodeset = $xpath->query($query, $parentRef);
            if ($sObject = $nodeset->item(0)) {
                $sObject->parentNode->insertBefore($keyInfo, $sObject);
                $inserted = true;
            }

            if (!$inserted) {
                $parentRef->appendChild($keyInfo);
            }
        }

        $keyInfo->appendChild($node);

        return $keyInfo;
    }

    /**
     * Esta función recupera una matriz asociativa de los nodos validados.
     *
     * La matriz contendrá la identificación del nodo al que se hace referencia como la clave y el nodo en sí
     * como el valor.
     *
     * Devoluciones:
     * Una matriz asociativa de nodos validados o nula si no se han validado nodos.
     *
     *  @return array Matriz asociativa de nodos validados
     */
    public function getValidatedNodes()
    {
        return $this->validatedNodes;
    }
}
