<?php
namespace Payum\Request;

class NotifyRequest
{
    /**
     * @var array
     */
    protected $notification;

    /**
     * @param array $notification
     */
    public function __construct(array $notification)
    {
        $this->notification = $notification;
    }

    /**
     * @return array
     */
    public function getNotification()
    {
        return $this->notification;
    }
}