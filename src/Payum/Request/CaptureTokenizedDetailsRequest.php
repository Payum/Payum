<?php
namespace Payum\Request;

use Payum\Model\TokenizedDetails;
use Payum\Request\CaptureRequest;

class CaptureTokenizedDetailsRequest extends CaptureRequest
{
    /**
     * @var \Payum\Model\TokenizedDetails
     */
    protected $tokenizedDetails;

    /**
     * @param \Payum\Model\TokenizedDetails $tokenizedDetails
     */
    public function __construct(TokenizedDetails $tokenizedDetails)
    {
        $this->tokenizedDetails = $tokenizedDetails;

        $this->setModel($tokenizedDetails);
    }

    /**
     * @return \Payum\Model\TokenizedDetails
     */
    public function getTokenizedDetails()
    {
        return $this->tokenizedDetails;
    }
}