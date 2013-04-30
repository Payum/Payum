<?php
namespace Payum\Model;

use Payum\Exception\InvalidArgumentException;
use Payum\Storage\Identificator;
use Payum\Util\Random;

class TokenizedDetails implements DetailsAggregateInterface, DetailsAwareInterface 
{
    /**
     * @var Identificator
     */
    protected $details;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $paymentName;

    public function __construct()
    {
        $this->token = time().'-'.Random::generateToken();
    }

    /**
     * {@inheritdoc}
     * 
     * @return Identificator
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param Identificator $details
     * 
     * @throws InvalidArgumentException if $details is not instance of Identificator
     *
     * @return void
     */
    public function setDetails($details)
    {
        if (false == $details instanceof Identificator) {
            throw new InvalidArgumentException('Details must be instance of `Identificator`.');
        }

        $this->details = $details;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @return string
     */
    public function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * @param string $paymentName
     */
    public function setPaymentName($paymentName)
    {
        $this->paymentName = $paymentName;
    }
}