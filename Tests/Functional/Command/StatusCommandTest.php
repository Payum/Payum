<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Command;

use Payum\Bundle\PayumBundle\Command\StatusCommand;
use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class StatusCommandTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    /**
     * @test
     */
    public function shouldReturnNewStatus()
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $modelClass = 'Payum\Core\Model\ArrayObject';

        $storage = $payum->getStorageForClass($modelClass, 'offline');
        $model = $storage->createModel();
        $storage->updateModel($model);

        $modelId = $storage->getIdentificator($model)->getId();

        $output = $this->executeConsole(new StatusCommand, array(
            'payment-name' => 'offline',
            '--model-class' => $modelClass,
            '--model-id' => $modelId
        ));

        $this->assertContains("Status: new", $output);
    }

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @param string[]                                   $arguments
     * @param string[]                                   $options
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = array(), array $options = array())
    {
        $command->setApplication(new Application($this->client->getKernel()));
        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->client->getContainer());
        }

        $arguments = array_replace(array('command' => $command->getName()), $arguments);
        $options = array_replace(array('--env' => 'test'), $options);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments, $options);

        return $commandTester->getDisplay();
    }
}
