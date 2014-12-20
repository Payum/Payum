<?php
namespace Payum\Core\Exception\Http;

use Buzz\Message\Response;
use Buzz\Message\Request;
use Payum\Core\Exception\ExceptionInterface;

interface HttpExceptionInterface extends ExceptionInterface
{
    /**
     * @param Request $request
     *
     * @return void
     */
    public function setRequest(Request $request);

    /**
     * @return Request
     */
    public function getRequest();

    /**
     * @param Response $response
     *
     * @return void
     */
    public function setResponse(Response $response);

    /**
     * @return Response
     */
    public function getResponse();
}
