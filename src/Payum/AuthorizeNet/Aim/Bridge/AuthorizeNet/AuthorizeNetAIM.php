<?php
namespace Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet;

class AuthorizeNetAIM extends \AuthorizeNetAIM
{
    public $ignore_not_x_fields = false;

    /**
     * {@inheritdoc}
     */
    public function setField($name, $value)
    {
        // the _all_aim_fields is private so we cannot check that.
        try {
            parent::setField($name, $value);
        } catch (\AuthorizeNetException $e) {
            if ($this->ignore_not_x_fields) {
                return;
            }

            throw $e;
        }
    }
}