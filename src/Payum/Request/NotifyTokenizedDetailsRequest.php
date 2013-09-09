<?php
namespace Payum\Request;

/**
 * @deprecated since 0.6 will be removed in 0.7
 */
class NotifyTokenizedDetailsRequest extends SecuredNotifyRequest
{
    /**
     * @return \Payum\Model\TokenizedDetails
     */
    public function getTokenizedDetails()
    {
        return $this->getToken();
    }
}