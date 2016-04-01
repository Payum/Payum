<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api as BaseApi;
use Payum\Core\Exception\Http\HttpException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
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
        'token' => null,
        'tokenSecret' => null,
        'third_party_subject' => null,
        'sandbox' => null,
        'useraction' => null,
        'cmd' => self::CMD_EXPRESS_CHECKOUT,
    );

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
     * @param RequestInterface $request
     * @return RequestInterface
     */
    protected function authorizeRequest(RequestInterface $request)
    {
        $authSignature = $this->generateOauthSignature($request);
        return $request->withAddedHeader('X-PAYPAL-AUTHORIZATION', $authSignature);
    }

    /**
     * Generates OAuth signature for X-PAYPAL-AUTHORIZATION header.
     * @see https://developer.paypal.com/docs/classic/permissions-service/integration-guide/PermissionsUsing/
     *
     * @param RequestInterface $request
     * $throws LogicException
     * @return string
     */
    protected function generateOauthSignature(RequestInterface $request) {
        if (false == class_exists(AuthSignature::class)) {
            throw new LogicException('You must install "paypal/sdk-core-php:~3.0" library.');
        }
        return AuthSignature::generateFullAuthString(
            $this->options['username'],
            $this->options['password'],
            $this->options['token'],
            $this->options['tokenSecret'],
            $request->getMethod(),
            $this->getApiEndpoint()
        );
    }

}