<?php
namespace Payum\Klarna\Invoice;

class Config
{
    public $eid;

    public $secret;

    public $country = \KlarnaCountry::SE;

    public $language = \KlarnaLanguage::SV;

    public $currency = \KlarnaCurrency::SEK;

    public $server = \Klarna::BETA;

    public $pClassStorage = 'json';

    public $pClassStoragePath = './pclasses.json';
}