<?php
namespace Payum\Klarna\Checkout;

class GloabStateSafeConnector extends \Klarna_Checkout_BasicConnector
{
    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @param string $secret
     * @param string $baseUri
     * @param string $contentType
     * @param \Klarna_Checkout_HTTP_TransportInterface $transport
     * @param \Klarna_Checkout_Digest $digest
     */
    public function __construct(
        $secret,
        $baseUri = null,
        $contentType = null,
        \Klarna_Checkout_HTTP_TransportInterface $transport = null,
        \Klarna_Checkout_Digest $digest = null
    ) {
        $this->baseUri = $baseUri ?: 'https://checkout.testdrive.klarna.com/checkout/orders';
        $this->contentType = $contentType ?: 'application/vnd.klarna.checkout.aggregated-order-v2+json';

        parent::__construct(
            $transport ?: \Klarna_Checkout_HTTP_Transport::create(),
            $digest ?: new \Klarna_Checkout_Digest,
            $secret
        );
    }

    /**
     * {@inheritDoc}
     */
    public function apply($method, \Klarna_Checkout_ResourceInterface $resource, array $options = null)
    {
        $previousBaseUri = \Klarna_Checkout_Order::$baseUri;
        $previousContentType = \Klarna_Checkout_Order::$contentType;

        \Klarna_Checkout_Order::$baseUri = $this->baseUri;
        \Klarna_Checkout_Order::$contentType = $this->contentType;

        try {
            $result = parent::apply($method, $resource, $options);

            \Klarna_Checkout_Order::$baseUri = $previousBaseUri;
            \Klarna_Checkout_Order::$contentType = $previousContentType;

            return $result;
        } catch (\Exception $e) {
            \Klarna_Checkout_Order::$baseUri = $previousBaseUri;
            \Klarna_Checkout_Order::$contentType = $previousContentType;

            throw $e;
        }
    }
} 