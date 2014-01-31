<?php
namespace Payum\Klarna\Checkout;

class GlobalStateSafeConnector implements \Klarna_Checkout_ConnectorInterface
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
     * @var \Klarna_Checkout_ConnectorInterface
     */
    protected $internalConnector;

    /**
     * @param \Klarna_Checkout_ConnectorInterface $internalConnector
     * @param string $baseUri
     * @param string $contentType
     */
    public function __construct(
        \Klarna_Checkout_ConnectorInterface $internalConnector,
        $baseUri = null,
        $contentType = null
    ) {
        $this->baseUri = $baseUri ?: Constants::BASE_URI_SANDBOX;
        $this->contentType = $contentType ?: Constants::CONTENT_TYPE_V2_PLUS_JSON;
        $this->internalConnector = $internalConnector;
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
            $this->internalConnector->apply($method, $resource, $options);

            \Klarna_Checkout_Order::$baseUri = $previousBaseUri;
            \Klarna_Checkout_Order::$contentType = $previousContentType;
        } catch (\Exception $e) {
            \Klarna_Checkout_Order::$baseUri = $previousBaseUri;
            \Klarna_Checkout_Order::$contentType = $previousContentType;

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getTransport()
    {
        return $this->internalConnector->getTransport();
    }
}
