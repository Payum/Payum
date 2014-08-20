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
    './pclasses.json'  // PClass storage URI path
);


// Method: addArticle

// Handling fee, price including VAT.
$flags = KlarnaFlags::INC_VAT | KlarnaFlags::IS_HANDLING;
$k->addArticle(
    4,              // Quantity
    "HANDLING",     // Article number
    "Handling fee", // Article name/title
    50.99,          // Price
    25,             // 25% VAT
    0,              // Discount
    $flags          // Flags
);


// Method: setAddress

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


// Method: calcMonthlyCost

$amount = 149.99;
$pclass = $k->getCheapestPClass($amount, KlarnaFlags::PRODUCT_PAGE);
$value = null;
if ($pclass) {
    $monthly = KlarnaCalc::calc_monthly_cost($amount, $pclass, KlarnaFlags::PRODUCT_PAGE);

    echo "monthly cost: {$monthly}\n";
}


// Method: totalCreditPurchaseCost

$id = 100;
$pclass = $k->getPClass($id);
$amount = 100.50;
if ($pclass) {
    $total = KlarnaCalc::total_credit_purchase_cost($amount, $pclass, KlarnaFlags::CHECKOUT_PAGE);

    echo "total credit purchase cost: {$total}\n";
}


// Method: calcAPR

$id = 100;
$pclass = $k->getPClass($id);
$amount = 105.50;
if ($pclass) {
    $apr = KlarnaCalc::calc_apr($amount, $pclass, KlarnaFlags::CHECKOUT_PAGE);

    echo "apr: {$apr}\n";
}


// Method: getPClasses

// Optional argument PClass type to filter by.
// E.g. KlarnaPClass::CAMPAIGN
$pclasses = $k->getPClasses();

// $pclasses is now a list of KlarnaPClass instances.
