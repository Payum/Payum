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

$addr = new KlarnaAddr(
    'always_approved@klarna.com', // Email address
    '',                           // Telephone number, only one phone number is needed
    '0762560000',                 // Cell phone number
    'Testperson-se',              // First name (given name)
    'Approved',                   // Last name (family name)
    '',                           // No care of, C/O
    'Stårgatan 1',                // Street address
    '12345',                      // Zip code
    'Ankeborg',                   // City
    KlarnaCountry::SE,            // Country
    null,                         // House number (AT/DE/NL only)
    null                          // House extension (NL only)
);

$k->setAddress(KlarnaFlags::IS_BILLING, $addr);
$k->setAddress(KlarnaFlags::IS_SHIPPING, $addr);

try {
    $result = $k->reserveAmount(
        '4103219202', // PNO (Date of birth for AT/DE/NL)
        null, // KlarnaFlags::MALE, KlarnaFlags::FEMALE (AT/DE/NL only)
        -1,   // Automatically calculate and reserve the cart total amount
        KlarnaFlags::NO_FLAG,
        KlarnaPClass::INVOICE
    );

    $rno = $result[0];
    $status = $result[1];

    // $status is KlarnaFlags::PENDING or KlarnaFlags::ACCEPTED.

    echo "OK: reservation {$rno} - order status {$status}\n";
} catch(Exception $e) {
    echo "{$e->getMessage()} (#{$e->getCode()})\n";
}
