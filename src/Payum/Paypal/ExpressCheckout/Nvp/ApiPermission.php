<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api as BaseApi;
use Payum\Core\Exception\Http\HttpException;
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

    /**
     * @var array
     */
    protected $options = array(
        'username' => null,
        'password' => null,
        'signature' => null,
        'return_url' => null,
        'cancel_url' => null,
        'token' => null,
        'token_secret' => null,
        'third_party_subject' => null,
        'sandbox' => null,
        'useraction' => null,
        'cmd' => self::CMD_EXPRESS_CHECKOUT,
    );

    /**
     * {@inheritDoc}
     */
    protected function authorizeRequest(RequestInterface $request)
    {
        $request = parent::authorizeRequest($request);

        $fields = array();
        parse_str($request->getBody(), $fields);
        $fields['SUBJECT'] = $this->options['third_party_subject'];
        $request->setBody(http_build_query($fields));
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
    protected function generateOauthSignature(RequestInterface $request)
    {
        return AuthSignature::generateFullAuthString(
            $this->options['username'],
            $this->options['password'],
            $this->options['token'],
            $this->options['token_secret'],
            $request->getMethod(),
            $this->getApiEndpoint()
        );
    }

}