<?php
namespace Payum\Paypal\ProCheckout\Nvp\Exception\Http;

use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Request;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Response;
use Payum\Core\Exception\Http\HttpException;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class HttpResponseNotSuccessException extends HttpException
{
  public function __construct(Request $request, Response $response, $message = "", $code = 0, \Exception $previous = null)
  {
    if (false == $message) {
      $message = sprintf(
        "The response is not success.\n Response: %s",//\n Request: %s
        print_r($response->toArray(), true)/*,
        print_r($request->getFields(), true)*/ // It is not secure to show user's info
      );
    }

    parent::__construct($request, $response, $message, $code, $previous);
  }
}
