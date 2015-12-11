<?php
namespace Payum\Klarna\Invoice\Tests\Action\Api;

use Payum\Core\Tests\SkipOnPhp7Trait;
use Payum\Klarna\Invoice\Action\Api\PopulateKlarnaFromDetailsAction;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;

class PopulateKlarnaFromDetailsActionTest extends \PHPUnit_Framework_TestCase
{
    use SkipOnPhp7Trait;

    /**
     * @test
     */
    public function shouldImplementsActionInterface()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Action\Api\PopulateKlarnaFromDetailsAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\ActionInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new PopulateKlarnaFromDetailsAction();
    }

    /**
     * @test
     */
    public function shouldSupportPopulateKlarnaFromDetails()
    {
        $action = new PopulateKlarnaFromDetailsAction();

        $this->assertTrue($action->supports(new PopulateKlarnaFromDetails(new \ArrayObject(), new \Klarna())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotPopulateKlarnaFromDetails()
    {
        $action = new PopulateKlarnaFromDetailsAction();

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldPopulateKlarnaFromDetails()
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

        $klarna = new \Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute($request);

        //Klarna does not provide a way to get data from its object. So we just test that there werent any errors.
    }

    /**
     * @test
     */
    public function shouldNotFaileIfEmptyDetailsGiven()
    {
        $klarna = new \Klarna();

        $request = new PopulateKlarnaFromDetails(new \ArrayObject(), $klarna);

        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute($request);

        //Klarna does not provide a way to get data from its object. So we just test that there werent any errors.
    }

    /**
     * @test
     */
    public function shouldCorrectlyPutPartialArticles()
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

        $klarna = new \Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $action = new PopulateKlarnaFromDetailsAction();

        $action->execute($request);

        //Klarna does not provide a way to get data from its object. So we just test that there werent any errors.
    }
}
