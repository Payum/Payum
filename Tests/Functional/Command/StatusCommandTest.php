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
     * @test
     */
    public function shouldReturnNewStatus()
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $modelClass = 'Payum\Core\Model\ArrayObject';

        $storage = $payum->getStorage($modelClass);
        $model = $storage->create();
        $storage->update($model);

        $modelId = $storage->identify($model)->getId();

        $output = $this->executeConsole(new StatusCommand, array(
            'gateway-name' => 'fooGateway',
            '--model-class' => $modelClass,
            '--model-id' => $modelId
        ));

        $this->assertContains("Status: new", $output);
    }

    /**
     * @param Command  $command
     * @param string[] $arguments
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = array())
    {
        $command->setApplication(new Application($this->client->getKernel()));
        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->client->getContainer());
        }

        $arguments = array_replace(array(
            '--env' => 'test',
            'command' => $command->getName()
        ), $arguments);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments);

        return $commandTester->getDisplay();
    }
}
