<?php

require_once dirname(dirname(__FILE__)) . '/Klarna.php';

// Dependencies from http://phpxmlrpc.sourceforge.net/
require_once dirname(dirname(__FILE__)) .
    '/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc';
require_once dirname(dirname(__FILE__)) .
    '/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc';

$k = new Klarna();

$k->config(
    0,                    // Merchant ID
    'sharedSecret',       // Shared secret
    KlarnaCountry::SE,    // Purchase country
    KlarnaLanguage::SV,   // Purchase language
    KlarnaCurrency::SEK,  // Purchase currency
    Klarna::BETA,         // Server
    'json',               // PClass storage
    './pclasses.json'     // PClass storage URI path
);

$rno = '123456';

try {
    $result = $k->activate($rno, null, KlarnaFlags::RSRV_SEND_BY_EMAIL);

    // For optional arguments, flags, partial activations and so on, refer to the documentation.
    // See Klarna::setActivateInfo

    $risk = $result[0];  // "ok" or "no_risk"
    $invNo = $result[1]; // "9876451"

    echo "OK: invoice number {$invNo} - risk status {$risk}\n";
} catch(Exception $e) {
    echo "{$e->getMessage()} (#{$e->getCode()})\n";
}
