<?php
namespace Payum\Paypal\ExpressCheckout\NvpViaToken;

use Payum\Paypal\ExpressCheckout\Nvp\Api as BaseApi;
use Payum\Core\Exception\Http\HttpException;
use GuzzleHttp\Psr7\Request;
use PayPal\Auth\Oauth\AuthSignature;

/**
 * {@inheritDoc}
 *
 * @link https://developer.paypal.com/docs/classic/products/permissions/
 * @link https://developer.paypal.com/docs/classic/permissions-service/integration-guide/PermissionsUsing/
 *
 */
class ApiPermission extends BaseApi
{

    protected $options = array(
        'username' => null,
        'password' => null,
        'signature' => null,
        'return_url' => null,
        'cancel_url' => null,
        'sandbox' => null,
        'useraction' => null,
        'token' => null,
        'tokenSecret' => null,
        'cmd' => self::CMD_EXPRESS_CHECKOUT,
    );

    /**
     * @param array $fields
     *
     * @throws HttpException
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $method = 'POST';

        $request = new Request($method, $this->getApiEndpoint(), $headers, http_build_query($fields));
        $request = $this->authorizeRequest($request);

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = array();
        parse_str($response->getBody()->getContents(), $result);
        foreach ($result as &$value) {
            $value = urldecode($value);
        }

        return $result;
    }

    /**
     * @param array $fields
     */
    protected function addAuthorizeFields(array &$fields)
    {
        $fields['PWD'] = $this->options['password'];
        $fields['USER'] = $this->options['username'];
        $fields['SIGNATURE'] = $this->options['signature'];

        $fields['SUBJECT'] = $this->options['third_party_subject'];
    }

    /**
     * @deprecated
     * @param array $headers
     * @param string $method='POST'
     */
    protected function addAuthorizeHeader(array &$headers, $method = 'POST')
    {
    }

    /**
     * Adds authorize headers to request.
     * Note: only headers.
     *
     * @param Request $request
     * @return Request
     */
    protected function authorizeRequest(Request $request)
    {
        $authSignature = AuthSignature::generateFullAuthString(
            $this->options['username'],
            $this->options['password'],
            $this->options['token'],
            $this->options['tokenSecret'],
            $request->getMethod(),
            $this->getApiEndpoint()
        );
        return $request->withAddedHeader('X-PAYPAL-AUTHORIZATION', $authSignature);
    }

}