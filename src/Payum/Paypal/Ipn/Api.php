<?php
namespace Payum\Paypal\Ipn;

use Buzz\Client\ClientInterface;
use Buzz\Message\Form\FormRequest;
use Buzz\Message\Response;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;

/**
 * @link https://www.x.com/developers/paypal/documentation-tools/ipn/integration-guide/IPNIntro
 */
class Api 
{
    /**
     * It sends back if the message originated with PayPal.
     */
    const NOTIFY_VERIFIED = 'VERIFIED';

    /**
     * if there is any discrepancy with what was originally sent
     */
    const NOTIFY_INVALID = 'INVALID';
    
    const CMD_NOTIFY_VALIDATE = '_notify-validate';

    /**
     * @var \Buzz\Client\ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param ClientInterface $client
     * @param array $options
     */
    public function __construct(ClientInterface $client, array $options)
    {
        $this->client = $client;
        
        $this->options = $options;

        if (false == (isset($this->options['sandbox']) && is_bool($this->options['sandbox']))) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }
    }
    
    /**
     * @param array $notification
     * 
     * @return string
     */
    public function notifyValidate(array $notification)
    {
        $request = new FormRequest();
        $request->setField('cmd', self::CMD_NOTIFY_VALIDATE);
        $request->addFields($notification);
        $request->setMethod('POST');
        $request->fromUrl($this->getIpnEndpoint());
        
        $response = new Response;
        
        $this->client->send($request, $response);

        if (false == $response->isSuccessful()) {
            throw HttpException::factory($request, $response);
        }
        
        if (self::NOTIFY_VERIFIED === $response->getContent()) {
            return self::NOTIFY_VERIFIED;
        }
        
        return self::NOTIFY_INVALID;
    }

    /**
     * @return string
     */
    public function getIpnEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://www.sandbox.paypal.com/cgi-bin/webscr' :
            'https://www.paypal.com/cgi-bin/webscr'
        ;
    }
}