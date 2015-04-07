<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\Command;

use Payum\Bundle\PayumBundle\Command\CreateNotifyTokenCommand;
use Payum\Bundle\PayumBundle\Tests\Functional\WebTestCase;
use Payum\Core\Registry\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CreateNotifyTokenCommandTest extends WebTestCase
{
    /**
     * @test
     */
    public function shouldCreateNotifyTokenWithoutModel()
    {
        $output = $this->executeConsole(new CreateNotifyTokenCommand, array(
            'gateway-name' => 'fooGateway'
        ));

        $this->assertContains('Hash: ', $output);
        $this->assertContains('Url: ', $output);
        $this->assertContains('Details: null', $output);
    }

    /**
     * @test
     */
    public function shouldCreateNotifyTokenWithModel()
    {
        /** @var RegistryInterface $payum */
        $payum = $this->client->getContainer()->get('payum');

        $modelClass = 'Payum\Core\Model\ArrayObject';

        $storage = $payum->getStorage($modelClass);
        $model = $storage->create();
        $storage->update($model);

        $modelId = $storage->identify($model)->getId();

        $output = $this->executeConsole(new CreateNotifyTokenCommand, array(
            'gateway-name' => 'fooGateway',
            '--model-class' => $modelClass,
            '--model-id' => $modelId
        ));

        $this->assertContains('Hash: ', $output);
        $this->assertContains('Url: ', $output);
        $this->assertContains("Details: $modelClass#$modelId", $output);
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
