<?php

namespace Payum\Klarna\Invoice\Request\Api;

use ArrayAccess;
use Klarna;
use Payum\Core\Request\Generic;

class PopulateKlarnaFromDetails extends Generic
{
    /**
     * @var Klarna
     */
    protected $klarna;

    public function __construct(ArrayAccess $details, Klarna $klarna)
    {
        parent::__construct($details);

        $this->klarna = $klarna;
    }

    public function getKlarna(): \Klarna
    {
        return $this->klarna;
    }
}
