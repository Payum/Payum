<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Command;

use Payum\Bundle\PayumBundle\Command\DebugPaymentCommand;
use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DebugPaymentCommandTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldOutputDebugInfoAboutSinglePayment()
    {
        $output = $this->executeConsole(new DebugPaymentCommand(), array(
            'payment-name' => 'fooPayment',
        ));

        $this->assertContains('Found 1 payments', $output);
        $this->assertContains('fooPayment (Payum\Core\Payment):', $output);
        $this->assertContains('Actions:', $output);
        $this->assertContains('Extensions:', $output);
        $this->assertContains('Apis:', $output);

        $this->assertContains('Payum\Offline\Action\CaptureAction', $output);

        $this->assertContains('Payum\Core\Extension\StorageExtension', $output);
        $this->assertContains('Payum\Core\Storage\FilesystemStorage', $output);
        $this->assertContains('Payum\Core\Model\ArrayObject', $output);
    }

    /**
     * @test
     */
    public function shouldOutputDebugInfoAboutAllPayments()
    {
        $output = $this->executeConsole(new DebugPaymentCommand());

        $this->assertContains('Found 2 payments', $output);
        $this->assertContains('fooPayment (Payum\Core\Payment):', $output);
        $this->assertContains('barPayment (Payum\Core\Payment):', $output);
    }

    /**
     * @test
     */
    public function shouldOutputInfoWhatActionsSupports()
    {
        $output = $this->executeConsole(new DebugPaymentCommand(), array(
            'payment-name' => 'fooPayment',
            '--show-supports' => true,
        ));

        $this->assertContains('Found 1 payments', $output);
        $this->assertContains('fooPayment (Payum\Core\Payment):', $output);
        $this->assertContains('Payum\Offline\Action\CaptureAction', $output);
        $this->assertContains('$request instanceof Capture &&', $output);
        $this->assertContains('$request->getModel() instanceof OrderInterface', $output);
    }

    /**
     * @test
     */
    public function shouldOutputChoiceListPaymentsForNameGiven()
    {
        $command = new DebugPaymentCommand();
        $command->setApplication(new Application($this->client->getKernel()));

        $helperSet = $command->getHelperSet();
        if (!$helperSet->has('question')) {
            $this->markTestSkipped('The symfony have a version <2.5');
        }

        $helper = $helperSet->get('question');
        $helper->setInputStream($this->getInputStream('0'));

        $output = $this->executeConsole($command, array(
            'payment-name' => 'foo',
        ));

        $this->assertContains('Choose a number for more information on the payum payment', $output);
        $this->assertContains('[0] fooPayment', $output);
    }

    /**
     * @param Command  $command
     * @param string[] $arguments
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = array())
    {
        if (!$command->getApplication()) {
            $command->setApplication(new Application($this->client->getKernel()));
        }

        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->client->getContainer());
        }

        $arguments = array_replace(array(
            '--env' => 'test',
            'command' => $command->getName(),
        ), $arguments);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments);

        return $commandTester->getDisplay();
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
