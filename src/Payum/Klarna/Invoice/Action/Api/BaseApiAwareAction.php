<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Klarna\Invoice\Config;

abstract class BaseApiAwareAction implements ApiAwareInterface, ActionInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Klarna
     */
    private $klarna;

    /**
     * @param \Klarna $klarna
     */
    public function __construct(\Klarna $klarna = null)
    {
        $this->klarna = $klarna ?: new \Klarna();
    }

    /**
     * @param mixed $api
     *
     * @throws UnsupportedApiException if the given Api is not supported.
     */
    public function setApi($api)
    {
        if (false == $api instanceof Config) {
            throw new UnsupportedApiException('Instance of Config is expected to be passed as api.');
        }

        $this->config = $api;
    }

    /**
     * @return \Klarna
     */
    protected function getKlarna()
    {
        $this->klarna->config(
            $this->config->eid,
            $this->config->secret,
            $this->config->country,
            $this->config->language,
            $this->config->currency,
            $this->config->mode
        );

        $this->klarna->clear();

        $rp = new \ReflectionProperty($this->klarna, 'xmlrpc');
        $rp->setAccessible(true);
        /** @var \xmlrpc_client $xmlrpc */
        $xmlrpc = $rp->getValue($this->klarna);
        $xmlrpc->verifyhost = $this->config->xmlRpcVerifyHost;
        $xmlrpc->verifypeer = $this->config->xmlRpcVerifyPeer;
        $rp->setAccessible(false);

        return $this->klarna;
    }

    /**
     * @param \ArrayAccess     $details
     * @param \KlarnaException $e
     * @param object           $request
     */
    protected function populateDetailsWithError(\ArrayAccess $details, \KlarnaException $e, $request)
    {
        $details['error_request'] = get_class($request);
        $details['error_file'] = $e->getFile();
        $details['error_line'] = $e->getLine();
        $details['error_code'] = (int) $e->getCode();
        $details['error_message'] = utf8_encode($e->getMessage());
    }
}
