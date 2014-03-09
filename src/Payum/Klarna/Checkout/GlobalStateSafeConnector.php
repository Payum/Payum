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
    protected $merchantId;

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
     * @param string $merchantId
     * @param string $baseUri
     * @param string $contentType
     */
    public function __construct(
        \Klarna_Checkout_ConnectorInterface $internalConnector,
        $merchantId = null,
        $baseUri = null,
        $contentType = null
    ) {
        $this->merchantId = $merchantId;
        $this->baseUri = $baseUri ?: Constants::BASE_URI_SANDBOX;
        $this->contentType = $contentType ?: Constants::CONTENT_TYPE_V2_PLUS_JSON;
        $this->internalConnector = $internalConnector;
    }

    /**
     * {@inheritDoc}
     */
    public function apply($method, \Klarna_Checkout_ResourceInterface $resource, array $options = null)
    {
        $previousContentType = \Klarna_Checkout_Order::$contentType;

        $options['url'] = isset($options['url']) ? $options['url'] : $this->baseUri;
        \Klarna_Checkout_Order::$contentType = $this->contentType;

        if (false == isset($options['data']['merchant']['id']) && $this->merchantId) {
            $options['data']['merchant']['id'] = (string) $this->merchantId;
        }

        try {
            $this->internalConnector->apply($method, $resource, $options);

            \Klarna_Checkout_Order::$contentType = $previousContentType;
        } catch (\Exception $e) {
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
