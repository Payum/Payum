<?php
namespace Payum\Exception\Http;

use Buzz\Message\Request;
use Buzz\Message\Response;

class HttpResponseStatusNotSuccessfulException extends HttpException
{
    public function __construct(Request $request, Response $response, $message = "", $code = 0, \Exception $previous = null)
    {
        if (false == $message) {
            $message = sprintf('The response `%s` status is not success.', $response->getStatusCode());
        }
        
        parent::__construct($request, $response, $message, $code, $previous);   
    }
}
