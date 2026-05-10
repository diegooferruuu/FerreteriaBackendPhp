<?php

use Http\Helpers\XMLSecLibs\SignedXml;

$xmlPath = '20600995805-01-F001-1.xml';
$certPath = 'certifcate.pem'; // Antes convertir pfx -> pem (private+certificate keys)

$signer = new SignedXml();
$signer->setCertificateFromFile($certPath);
// or $signer->setCertificate('-----BEGIN RSA PRIVATE KEY-----.....');

$xmlSigned = $signer->signFromFile($xmlPath);
// or $signer->signXml('<Invoice>....');

file_put_contents("signed.xml", $xmlSigned);
