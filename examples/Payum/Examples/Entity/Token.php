<?php
namespace Payum\Examples\Entity;

use Doctrine\ORM\Mapping as ORM;

use Payum\Bridge\Doctrine\Entity\Token as BaseToken;

/**
 * @ORM\Entity
 */
class Token extends BaseToken
{
}