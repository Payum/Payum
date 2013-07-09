<?php
namespace Payum\Request;

use Payum\Model\TokenizedDetails;

class NotifyTokenizedDetailsRequest extends NotifyRequest implements ModelRequestInterface
{
    /**
     * @var array
     */
    protected $notification;

    /**
     * @var mixed
     */
    protected $model;

    /**
     * @var TokenizedDetails
     */
    protected $tokenizedDetails;

    /**
     * @param array $notification
     * @param TokenizedDetails $tokenizedDetails
     */
    public function __construct(array $notification, TokenizedDetails $tokenizedDetails)
    {
        parent::__construct($notification);
        
        $this->tokenizedDetails = $tokenizedDetails;
        $this->model = $tokenizedDetails;
    }

    /**
     * @return array
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * @return TokenizedDetails
     */
    public function getTokenizedDetails()
    {
        return $this->tokenizedDetails;
    }

    /** 
     * {@inheritDoc}  
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getModel()
    {
        return $this->model;
    }
}