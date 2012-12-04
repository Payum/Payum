<?php
namespace Payum\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Bridge\Doctrine\Entity\SimpleSell as BaseSimpleSell;

/**
 * @ORM\Entity
 */
class SimpleSell extends BaseSimpleSell
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id; 
}