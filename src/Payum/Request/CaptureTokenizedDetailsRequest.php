<?php
namespace Payum\Request;

use Payum\Model\TokenizedDetails;

/**
 * @deprecated since 0.6 will be removed in 0.7
 */
class CaptureTokenizedDetailsRequest extends SecuredCaptureRequest
{
    /**
     * @return \Payum\Model\TokenizedDetails
     */
    public function getTokenizedDetails()
    {
        return $this->getToken();
    }
}