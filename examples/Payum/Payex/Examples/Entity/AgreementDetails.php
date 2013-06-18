<?php
namespace Payum\Payex\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Payex\Bridge\Doctrine\Entity\AgreementDetails as BaseAgreementDetails;

/**
 * @ORM\Entity
 */
class AgreementDetails extends BaseAgreementDetails
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }
}