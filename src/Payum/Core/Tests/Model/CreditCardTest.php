<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\CreditCard;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Security\SensitiveValue;
use Payum\Core\Tests\TestCase;

class CreditCardTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass(CreditCard::class);

        $this->assertTrue($rc->implementsInterface(CreditCardInterface::class));
    }

    public function testShouldReturnNullOnNewCreditCard()
    {
        $card = new CreditCard();

        $this->assertNull($card->getToken());
        $this->assertNull($card->getBrand());
        $this->assertNull($card->getHolder());
        $this->assertNull($card->getMaskedHolder());
        $this->assertNull($card->getNumber());
        $this->assertNull($card->getMaskedNumber());
        $this->assertNull($card->getSecurityCode());
        $this->assertNull($card->getExpireAt());
    }

    public function testShouldAllowGetPreviouslySetBrand()
    {
        $card = new CreditCard();

        $card->setBrand('theBrand');

        $this->assertSame('theBrand', $card->getBrand());
    }

    public function testShouldAllowGetPreviouslySetToken()
    {
        $card = new CreditCard();

        $card->setToken('theToken');

        $this->assertSame('theToken', $card->getToken());
    }

    public function testShouldAllowGetPreviouslySetHolder()
    {
        $card = new CreditCard();

        $card->setHolder('Mahatma Gandhi');

        $this->assertSame('Mahatma Gandhi', $card->getHolder());
    }

    public function testShouldStoreHolderAsSensitiveValue()
    {
        $card = new CreditCard();

        $card->setHolder('Mahatma Gandhi');

        $value = $this->readAttribute($card, 'securedHolder');
        $this->assertInstanceOf(SensitiveValue::class, $value);
        $this->assertSame('Mahatma Gandhi', $value->peek());
    }

    public function testShouldAllowGetMaskedHolderWhenSetHolder()
    {
        $card = new CreditCard();

        $card->setHolder('Mahatma Gandhi');

        $this->assertSame('MXXXXXX XXndhi', $card->getMaskedHolder());
    }

    public function testShouldAllowChangeMaskedHolder()
    {
        $card = new CreditCard();

        $card->setHolder('Mahatma Gandhi');
        $card->setMaskedHolder('theMaskedHolder');

        $this->assertSame('Mahatma Gandhi', $card->getHolder());
        $this->assertSame('theMaskedHolder', $card->getMaskedHolder());
    }

    public function testShouldAllowGetPreviouslySetNumber()
    {
        $card = new CreditCard();

        $card->setNumber('1234 5678 1234 5678');

        $this->assertSame('1234 5678 1234 5678', $card->getNumber());
    }

    public function testShouldStoreNumberAsSensitiveValue()
    {
        $card = new CreditCard();

        $card->setNumber('1234 5678 1234 5678');

        $value = $this->readAttribute($card, 'securedNumber');
        $this->assertInstanceOf(SensitiveValue::class, $value);
        $this->assertSame('1234 5678 1234 5678', $value->peek());
    }

    public function testShouldAllowGetMaskedNumberWhenSetNumber()
    {
        $card = new CreditCard();

        $card->setNumber('1234 5678 1234 5678');

        $this->assertSame('1XXX XXXX XXXX 5678', $card->getMaskedNumber());
    }

    public function testShouldAllowChangeMaskedNumber()
    {
        $card = new CreditCard();

        $card->setNumber('1234 5678 1234 5678');
        $card->setMaskedNumber('theMaskedNumber');

        $this->assertSame('1234 5678 1234 5678', $card->getNumber());

        $this->assertSame('theMaskedNumber', $card->getMaskedNumber());
    }

    public function testShouldAllowGetPreviouslySetSecurityCode()
    {
        $card = new CreditCard();

        $card->setSecurityCode('theCode');

        $this->assertSame('theCode', $card->getSecurityCode());
    }

    public function testShouldStoreSecurityCodeAsSensitiveValue()
    {
        $card = new CreditCard();

        $card->setSecurityCode('123');

        $value = $this->readAttribute($card, 'securedSecurityCode');
        $this->assertInstanceOf(SensitiveValue::class, $value);
        $this->assertSame('123', $value->peek());
    }

    public function testShouldAllowGetPreviouslySetExpireAt()
    {
        $card = new CreditCard();

        $expected = new \DateTime();

        $card->setExpireAt($expected);

        $this->assertSame($expected, $card->getExpireAt());
    }

    public function testShouldStoreExpireAtAsSensitiveValue()
    {
        $card = new CreditCard();

        $expected = new \DateTime();

        $card->setExpireAt($expected);

        $value = $this->readAttribute($card, 'securedExpireAt');
        $this->assertInstanceOf(SensitiveValue::class, $value);
        $this->assertSame($expected, $value->peek());
    }
}
