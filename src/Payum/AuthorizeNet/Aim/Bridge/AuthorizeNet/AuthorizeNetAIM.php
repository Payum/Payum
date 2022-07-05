<?php

namespace Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet;

use AuthorizeNetException;

// this is a fix of crappy auto loading in authorize.net lib.
class_exists(AuthorizeNetException::class, true);

class AuthorizeNetAIM extends \AuthorizeNetAIM
{
    public $ignore_not_x_fields = false;

    public function setField($name, $value)
    {
        // the _all_aim_fields is private so we cannot check that.
        try {
            parent::setField($name, $value);
        } catch (AuthorizeNetException $e) {
            if ($this->ignore_not_x_fields) {
                return;
            }

            throw $e;
        }
    }
}
