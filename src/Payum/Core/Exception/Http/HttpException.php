<?php
namespace Payum\Core\Exception\Http;

use Payum\Core\Exception\RuntimeException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpException extends RuntimeException implements HttpExceptionInterface
{
    protected RequestInterface $request;
    protected ResponseInterface $response;

    /**
     * {@inheritDoc}
     */
    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public static function factory(RequestInterface $request, ResponseInterface $response): HttpException
    {
        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            $label = 'Client error response';
        } elseif ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            $label = 'Server error response';
        } else {
            $label = 'Unsuccessful response';
        }

        $message = implode(PHP_EOL, array(
            $label,
            '[status code] '.$response->getStatusCode(),
            '[reason phrase] '.$response->getReasonPhrase(),
            '[url] '.$request->getUri(),
        ));

        $e = new static($message, $response->getStatusCode());
        $e->setResponse($response);
        $e->setRequest($request);

        return $e;
    }
}
