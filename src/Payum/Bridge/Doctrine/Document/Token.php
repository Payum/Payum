<?php
namespace Payum\Bridge\Doctrine\Document;

use Payum\Model\Token as BaseToken;

class Token extends BaseToken
{
    protected $detailsSerialized;

    public function getDetails()
    {
        if (null === $this->details && $this->detailsSerialized) {
            $this->details = unserialize($this->detailsSerialized);
        }

        return $this->details;
    }

    public function setDetails($details)
    {
        parent::setDetails($details);

        $this->detailsSerialized = serialize($details);
    }
}