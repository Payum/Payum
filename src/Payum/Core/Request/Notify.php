<?php
namespace Payum\Core\Request;

class Notify extends BaseModelAware
{
    /**
     * @var array
     */
    protected $notification;

    /**
     * @param array $notification
     * @param mixed $model
     */
    public function __construct(array $notification, $model = null)
    {
        $this->notification = $notification;

        parent::__construct($model);
    }

    /**
     * @return array
     */
    public function getNotification()
    {
        return $this->notification;
    }
}