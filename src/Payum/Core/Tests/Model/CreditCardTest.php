<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\CreditCard;
use Payum\Core\Model\CreditCardInterface;

class CreditCardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Model\CreditCard');

        $this->assertTrue($rc->implementsInterface(CreditCardInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new CreditCard();
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetBrand()
    {
        $card = new CreditCard();

        $card->setBrand('theBrand');

        $this->assertEquals('theBrand', $card->getBrand());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetToken()
    {
        $card = new CreditCard();

        $card->setToken('theToken');

        $this->assertEquals('theToken', $card->getToken());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetHolder()
    {
        $card = new CreditCard();

        $card->setHolder('Mahatma Gandhi');

        $this->assertEquals('Mahatma Gandhi', $card->getHolder());
    }

    /**
     * @test
     */
    public function shouldAllowSecureHolder()
    {
        $card = new CreditCard();

        $card->setHolder('Mahatma Gandhi');

        $this->assertEquals('Mahatma Gandhi', $this->readAttribute($card, 'holder'));
        $this->assertEquals('Mahatma Gandhi', $card->getHolder());
        $this->assertNull($this->readAttribute($card, 'securedHolder'));

        $card->secure();

        $this->assertNull($this->readAttribute($card, 'holder'));

        $value = $this->readAttribute($card, 'securedHolder');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertEquals('Mahatma Gandhi', $value->peek());
        $this->assertEquals('Mahatma Gandhi', $card->getHolder());
    }

    /**
     * @test
     */
    public function shouldAllowGetMaskedHolderWhenSetHolder()
    {
        $card = new CreditCard();

        $card->setHolder('Mahatma Gandhi');

        $this->assertEquals('MXXXXXX XXndhi', $card->getMaskedHolder());
    }

    /**
     * @test
     */
    public function shouldAllowChangeMaskedHolder()
    {
        $card = new CreditCard();

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
        $card = new CreditCard();

        $card->setNumber('1234 5678 1234 5678');

        $this->assertEquals('1234 5678 1234 5678', $card->getNumber());
    }

    /**
     * @test
     */
    public function shouldAllowSecureNumber()
    {
        $card = new CreditCard();

        $card->setNumber('1234 5678 1234 5678');

        $this->assertEquals('1234 5678 1234 5678', $this->readAttribute($card, 'number'));
        $this->assertEquals('1234 5678 1234 5678', $card->getNumber());
        $this->assertNull($this->readAttribute($card, 'securedNumber'));

        $card->secure();

        $this->assertNull($this->readAttribute($card, 'number'));

        $value = $this->readAttribute($card, 'securedNumber');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertEquals('1234 5678 1234 5678', $value->peek());
        $this->assertEquals('1234 5678 1234 5678', $card->getNumber());
    }

    /**
     * @test
     */
    public function shouldAllowGetMaskedNumberWhenSetNumber()
    {
        $card = new CreditCard();

        $card->setNumber('1234 5678 1234 5678');

        $this->assertEquals('1XXX XXXX XXXX 5678', $card->getMaskedNumber());
    }

    /**
     * @test
     */
    public function shouldAllowChangeMaskedNumber()
    {
        $card = new CreditCard();

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
        $card = new CreditCard();

        $card->setSecurityCode('theCode');

        $this->assertEquals('theCode', $card->getSecurityCode());
    }

    /**
     * @test
     */
    public function shouldAllowSecureSecurityCode()
    {
        $card = new CreditCard();

        $card->setSecurityCode('123');

        $this->assertEquals('123', $this->readAttribute($card, 'securityCode'));
        $this->assertEquals('123', $card->getSecuritycode());
        $this->assertNull($this->readAttribute($card, 'securedSecurityCode'));

        $card->secure();

        $this->assertNull($this->readAttribute($card, 'securityCode'));

        $value = $this->readAttribute($card, 'securedSecurityCode');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertEquals('123', $value->peek());
        $this->assertEquals('123', $card->getSecuritycode());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetExpireAt()
    {
        $card = new CreditCard();

        $expected = new \DateTime();

        $card->setExpireAt($expected);

        $this->assertSame($expected, $card->getExpireAt());
    }

    /**
     * @test
     */
    public function shouldAllowSecureExpireAt()
    {
        $card = new CreditCard();

        $expected = new \DateTime();

        $card->setExpireAt($expected);

        $this->assertEquals($expected, $this->readAttribute($card, 'expireAt'));
        $this->assertEquals($expected, $card->getExpireAt());
        $this->assertNull($this->readAttribute($card, 'securedExpireAt'));

        $card->secure();

        $this->assertNull($this->readAttribute($card, 'expireAt'));

        $value = $this->readAttribute($card, 'securedExpireAt');

        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $value);
        $this->assertEquals($expected, $value->peek());
        $this->assertEquals($expected, $card->getExpireAt());
    }

    /**
     * @test
     */
    public function shouldSecureOnlyChangedFields()
    {
        $card = new CreditCard();
        $card->setNumber('1234');

        $card->secure();

        //guard
        $this->assertNull($this->readAttribute($card, 'number'));
        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $this->readAttribute($card, 'securedNumber'));

        $card->setNumber('John Doe');

        $card->secure();

        $this->assertNull($this->readAttribute($card, 'holder'));
        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $this->readAttribute($card, 'securedHolder'));

        $this->assertNull($this->readAttribute($card, 'number'));
        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $this->readAttribute($card, 'securedNumber'));
    }
}
