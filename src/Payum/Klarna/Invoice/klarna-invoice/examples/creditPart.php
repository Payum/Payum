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

$invNo = '123456';

// Specify for which article(s) you want to refund.
$k->addArtNo(1, 'MG200MMS');

// Adding a return fee is possible. If you are interested in this
// functionality, make sure to always be in contact with Klarna before
// integrating return fees.

// $k->addArticle(
//     1,
//     "",
//     "Restocking fee",
//     11.5,
//     25,
//     0,
//     KlarnaFlags::NO_FLAG
// );

try {
    $k->creditPart($invNo);

    echo "OK\n";
} catch(Exception $e) {
    echo "{$e->getMessage()} (#{$e->getCode()})\n";
}
