<?php
namespace Payum\Core\Tests\Mocks\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\Token as BaseToken;

/**
 * @Mongo\Document
 */
class Token extends BaseToken
{
}
