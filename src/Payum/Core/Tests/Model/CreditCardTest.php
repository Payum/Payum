<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\CreditCard;

class CreditCardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Model\CreditCard');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Model\CreditCardInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreditCard;
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetBrand()
    {
        $card = new CreditCard;

        $card->setBrand('theBrand');

        $this->assertEquals('theBrand', $card->getBrand());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetToken()
    {
        $card = new CreditCard;

        $card->setToken('theToken');

        $this->assertEquals('theToken', $card->getToken());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetHolder()
    {
        $card = new CreditCard;

        $card->setHolder('Mahatma Gandhi');

        $this->assertEquals('Mahatma Gandhi', $card->getHolder());
    }

    /**
     * @test
     */
    public function shouldKeepHolderInternallyWrappedBySensitiveValue()
    {
        $card = new CreditCard;

        $card->setHolder('Mahatma Gandhi');

        $value = $this->readAttribute($card, 'holder');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertEquals('Mahatma Gandhi', $value->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetMaskedHolderWhenSetHolder()
    {
        $card = new CreditCard;

        $card->setHolder('Mahatma Gandhi');

        $this->assertEquals('MXXXXXX XXndhi', $card->getMaskedHolder());
    }

    /**
     * @test
     */
    public function shouldAllowChangeMaskedHolder()
    {
        $card = new CreditCard;

        $card->setHolder('Mahatma Gandhi');
        $card->setMaskedHolder('theMaskedHolder');

        $this->assertEquals('Mahatma Gandhi', $card->getHolder());
        $this->assertEquals('theMaskedHolder', $card->getMaskedHolder());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetNumber()
    {
        $card = new CreditCard;

        $card->setNumber('1234 5678 1234 5678');

        $this->assertEquals('1234 5678 1234 5678', $card->getNumber());
    }

    /**
     * @test
     */
    public function shouldKeepNumberInternallyWrappedBySensitiveValue()
    {
        $card = new CreditCard;

        $card->setNumber('1234 5678 1234 5678');

        $value = $this->readAttribute($card, 'number');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertEquals('1234 5678 1234 5678', $value->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetMaskedNumberWhenSetNumber()
    {
        $card = new CreditCard;

        $card->setNumber('1234 5678 1234 5678');

        $this->assertEquals('1XXX XXXX XXXX 5678', $card->getMaskedNumber());
    }

    /**
     * @test
     */
    public function shouldAllowChangeMaskedNumber()
    {
        $card = new CreditCard;

        $card->setNumber('1234 5678 1234 5678');
        $card->setMaskedNumber('theMaskedNumber');

        $this->assertEquals('1234 5678 1234 5678', $card->getNumber());

        $this->assertEquals('theMaskedNumber', $card->getMaskedNumber());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetSecurityCode()
    {
        $card = new CreditCard;

        $card->setSecurityCode('theCode');

        $this->assertEquals('theCode', $card->getSecurityCode());
    }

    /**
     * @test
     */
    public function shouldKeepSecurityCodeInternallyWrappedBySensitiveValue()
    {
        $card = new CreditCard;

        $card->setSecurityCode('123');

        $value = $this->readAttribute($card, 'securityCode');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertEquals('123', $value->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetExpireAt()
    {
        $card = new CreditCard;

        $expected = new \DateTime;

        $card->setExpireAt($expected);

        $this->assertSame($expected, $card->getExpireAt());
    }

    /**
     * @test
     */
    public function shouldKeepExiSecurityCodeInternallyWrappedBySensitiveValue()
    {
        $card = new CreditCard;

        $expected = new \DateTime;

        $card->setExpireAt($expected);

        $value = $this->readAttribute($card, 'expireAt');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertSame($expected, $value->peek());
    }
}
