<?php
namespace Payum\Klarna\Checkout;

class Config
{
    /**
     * @var string
     */
    public $merchantId;

    /**
     * @var string
     */
    public $secret;

    /**
     * @var int
     */
    public $baseUri = Constants::BASE_URI_SANDBOX;

    /**
     * @var string
     */
    public $termsUri;

    /**
     * @var string
     */
    public $checkoutUri;
}
