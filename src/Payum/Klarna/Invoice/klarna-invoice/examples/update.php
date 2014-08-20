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

// Replace cart with new items

$k->addArticle(
    4,                      // Quantity
    "MG200MMS",             // Article number
    "Matrox G200 MMS",      // Article name/title
    299.99,                 // Price
    25,                     // 25% VAT
    0,                      // Discount
    KlarnaFlags::INC_VAT    // Price is including VAT.
);

$k->addArticle(1, "", "Shipping fee", 14.5, 25, 0, KlarnaFlags::INC_VAT | KlarnaFlags::IS_SHIPMENT);
$k->addArticle(1, "", "Handling fee", 11.5, 25, 0, KlarnaFlags::INC_VAT | KlarnaFlags::IS_HANDLING);

// For information on what else you can update, refer to the documentation

$rno = '123456';

try {
    $k->update($rno);

    echo "OK\n";
} catch(KlarnaException $e) {
    echo "{$e->getMessage()} (#{$e->getCode()})\n";
}
