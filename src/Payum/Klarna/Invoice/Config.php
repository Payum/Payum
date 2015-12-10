<?php
namespace Payum\Klarna\Invoice;

class Config
{
    /**
     * @var string
     */
    public $eid;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var int
     */
    public $country = \KlarnaCountry::SE;

    /**
     * @var int
     */
    public $language = \KlarnaLanguage::SV;

    /**
     * @var int
     */
    public $currency = \KlarnaCurrency::SEK;

    /**
     * @var int
     */
    public $mode = \Klarna::BETA;

    /**
     * @var string
     */
    public $pClassStorage = 'json';

    /**
     * @var string
     */
    public $pClassStoragePath = './pclasses.json';

    /**
     * @var int
     */
    public $xmlRpcVerifyHost = 2;

    /**
     * @var bool
     */
    public $xmlRpcVerifyPeer = true;
}
