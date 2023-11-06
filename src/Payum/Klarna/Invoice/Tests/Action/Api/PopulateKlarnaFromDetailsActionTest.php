<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Klarna\Invoice\Action\Api\PopulateKlarnaFromDetailsAction;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;
use PHPUnit\Framework\TestCase;

class PopulateKlarnaFromDetailsActionTest extends TestCase
{
    public function testShouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\PopulateKlarnaFromDetailsAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    public function testShouldSupportPopulateKlarnaFromDetails()
    {
        $action = new PopulateKlarnaFromDetailsAction();

        $this->assertTrue($action->supports(new PopulateKlarnaFromDetails(new \ArrayObject(), new \Klarna())));
    }

    public function testShouldNotSupportAnythingNotPopulateKlarnaFromDetails()
    {
        $action = new PopulateKlarnaFromDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $this->expectException(\Payum\Core\Exception\RequestNotSupportedException::class);
        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute(new \stdClass());
    }

    public function testShouldPopulateKlarnaFromDetails()
    {
        $details = new \ArrayObject(array(
            'pno' => '410321-9202',
            'amount' => -1,
            'gender' => 1,
            'articles' => array(
                array(
                    'qty' => 4,
                    'artNo' => 'HANDLING',
                    'title' => 'Handling fee',
                    'price' => '50.99',
                    'vat' => '25',
                    'discount' => '0',
                    'flags' => 48,
                ),
            ),
            'billing_address' => array(
                'email' => 'info@payum.com',
                'telno' => '0700 00 00 00',
                'cellno' => '',
                'fname' => 'Testperson-se',
                'lname' => 'Approved',
                'company' => '',
                'careof' => '',
                'street' => 'Stårgatan 1',
                'house_number' => '',
                'house_extension' => '',
                'zip' => '12345',
                'city' => 'Ankeborg',
                'country' => 209,
            ),
            'shipping_address' => array(
                'email' => 'info@payum.com',
                'telno' => '0700 00 00 00',
                'cellno' => '',
                'fname' => 'Testperson-se',
                'lname' => 'Approved',
                'company' => '',
                'careof' => '',
                'street' => 'Stårgatan 1',
                'house_number' => '',
                'house_extension' => '',
                'zip' => '12345',
                'city' => 'Ankeborg',
                'country' => 209,
            ),
            'estore_info' => array(
                'order_id1' => 'anId',
                'order_id2' => 'anId',
                'username' => 'aName',
            ),
            'comment' => 'aComment',
        ));

        $klarna = $this->createMock(\Klarna::class);

        $klarna->expects($this->once())
            ->method('setComment')
            ->with('aComment');

        $klarna->expects($this->once())
            ->method('addArticle')
            ->with(4, 'HANDLING', 'Handling fee', '50.99', '25', '0', 48);

        $klarna->expects($this->atMost(2))
            ->method('setAddress')
            ->withConsecutive(
                [\KlarnaFlags::IS_SHIPPING, new \KlarnaAddr('info@payum.com', '0700 00 00 00', '', 'Testperson-se', 'Approved', '', utf8_decode('Stårgatan 1'), '12345', 'Ankeborg', 209, '', '')],
                [\KlarnaFlags::IS_BILLING, new \KlarnaAddr('info@payum.com', '0700 00 00 00', '', 'Testperson-se', 'Approved', '', utf8_decode('Stårgatan 1'), '12345', 'Ankeborg', 209, '', '')]
            );

        $klarna->expects($this->once())
            ->method('setEstoreInfo')
            ->with('anId', 'anId', 'aName');

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute($request);

        //Klarna does not provide a way to get data from its object. So we just test that there werent any errors.
    }

    public function testShouldNotFaileIfEmptyDetailsGiven()
    {
        $klarna = $this->createMock(\Klarna::class);

        $klarna->expects($this->once())
            ->method('setComment')
            ->with(null);

        $request = new PopulateKlarnaFromDetails(new \ArrayObject(), $klarna);

        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute($request);

        //Klarna does not provide a way to get data from its object. So we just test that there werent any errors.
    }

    public function testShouldCorrectlyPutPartialArticles()
    {
        $details = new \ArrayObject(array(
            'partial_articles' => array(
                array(
                    'qty' => 4,
                    'artNo' => 'HANDLING',
                    'title' => 'Handling fee',
                    'price' => '50.99',
                    'vat' => '25',
                    'discount' => '0',
                    'flags' => 48,
                ),
            ),
        ));

        $klarna = $this->createMock(\Klarna::class);

        $klarna->expects($this->once())
            ->method('addArtNo')
            ->with(4, 'HANDLING');

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute($request);

        //Klarna does not provide a way to get data from its object. So we just test that there werent any errors.
    }
}
