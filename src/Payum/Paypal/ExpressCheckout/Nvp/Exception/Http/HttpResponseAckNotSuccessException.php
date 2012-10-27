<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Exception\Http;

use Buzz\Message\Form\FormRequest;

use Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Exception\Http\HttpException;

class HttpResponseAckNotSuccessException extends HttpException
{
    public function __construct(FormRequest $request, Response $response, $message = "", $code = 0, \Exception $previous = null)
    {
        if (false == $message) {
            $message = sprintf('The response `%s` ack is not success.', $response['ACK']);
        }

        parent::__construct($request, $response, $message, $code, $previous);
    }
}