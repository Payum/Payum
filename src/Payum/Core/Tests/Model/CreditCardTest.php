<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\CreditCard;
use Payum\Core\Security\SensitiveValue;

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
    public function shouldAllowGetPreviouslySetStringCardHolder()
    {
        $card = new CreditCard;

        $card->setCardHolder('Mahatma Gandhi');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $card->getCardHolder());
        $this->assertEquals('Mahatma Gandhi', $card->getCardHolder()->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetSensitiveValueCardHolder()
    {
        $card = new CreditCard;

        $expected = new SensitiveValue('Mahatma Gandhi');

        $card->setCardHolder($expected);

        $this->assertSame($expected, $card->getCardHolder());
    }

    /**
     * @test
     */
    public function shouldAllowGetMaskedCardHolderWhenSetCardHolder()
    {
        $card = new CreditCard;

        $card->setCardHolder('Mahatma Gandhi');

        $this->assertEquals('MXXXXXX XXndhi', $card->getMaskedCardHolder());
    }

    /**
     * @test
     */
    public function shouldAllowChangeMaskedCardHolder()
    {
        $card = new CreditCard;

        $card->setCardHolder('Mahatma Gandhi');
        $card->setMaskedCardHolder('theMaskedCardHolder');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $card->getCardHolder());
        $this->assertEquals('Mahatma Gandhi', $card->getCardHolder()->peek());

        $this->assertEquals('theMaskedCardHolder', $card->getMaskedCardHolder());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetStringNumber()
    {
        $card = new CreditCard;

        $card->setNumber('1234 5678 1234 5678');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $card->getNumber());
        $this->assertEquals('1234 5678 1234 5678', $card->getNumber()->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetSensitiveValueNumber()
    {
        $card = new CreditCard;

        $expected = new SensitiveValue('1234 5678 1234 5678');

        $card->setNumber($expected);

        $this->assertSame($expected, $card->getNumber());
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

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $card->getNumber());
        $this->assertEquals('1234 5678 1234 5678', $card->getNumber()->peek());

        $this->assertEquals('theMaskedNumber', $card->getMaskedNumber());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetStringSecurityCode()
    {
        $card = new CreditCard;

        $card->setSecurityCode('theCode');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $card->getSecurityCode());
        $this->assertEquals('theCode', $card->getSecurityCode()->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetSensitiveValueSecurityCode()
    {
        $card = new CreditCard;
        
        $expected = new SensitiveValue('theCode');

        $card->setSecurityCode($expected);

        $this->assertSame($expected, $card->getSecurityCode());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetStringExpirationYear()
    {
        $card = new CreditCard;

        $card->setExpiryYear('theCode');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $card->getExpiryYear());
        $this->assertEquals('theCode', $card->getExpiryYear()->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetSensitiveValueExpirationYear()
    {
        $card = new CreditCard;

        $expected = new SensitiveValue('theCode');

        $card->setExpiryYear($expected);

        $this->assertSame($expected, $card->getExpiryYear());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetStringExpirationMonth()
    {
        $card = new CreditCard;

        $card->setExpiryMonth('theCode');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $card->getExpiryMonth());
        $this->assertEquals('theCode', $card->getExpiryMonth()->peek());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetSensitiveValueExpirationMonth()
    {
        $card = new CreditCard;

        $expected = new SensitiveValue('theCode');

        $card->setExpiryMonth($expected);

        $this->assertSame($expected, $card->getExpiryMonth());
    }
}
