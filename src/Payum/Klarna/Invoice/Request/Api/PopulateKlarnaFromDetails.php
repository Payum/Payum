<?php
namespace Payum\Klarna\Invoice\Request\Api;

use Payum\Core\Request\Generic;

class PopulateKlarnaFromDetails extends Generic
{
    /**
     * @var \Klarna
     */
    protected $klarna;

    /**
     * @param \ArrayAccess $details
     * @param \Klarna      $klarna
     */
    public function __construct(\ArrayAccess $details, \Klarna $klarna)
    {
        parent::__construct($details);

        $this->klarna = $klarna;
    }

    /**
     * @return \Klarna
     */
    public function getKlarna()
    {
        return $this->klarna;
    }
}
