<?php
namespace Payum\Exception\Http;

use Buzz\Message\Response;
use Buzz\Message\Request;
use Buzz\Exception\ExceptionInterface as BuzzExceptionInterface;

use Payum\Exception\ExceptionInterface;

interface HttpExceptionInterface extends ExceptionInterface, BuzzExceptionInterface 
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