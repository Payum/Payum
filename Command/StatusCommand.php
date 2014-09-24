<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Exception\RuntimeException;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\GetHumanStatus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('payum:status')
            ->setDescription('Allows to get a payment status.')
            ->addArgument('payment-name', InputArgument::REQUIRED, 'The payment name')
            ->addOption('model-class', null, InputOption::VALUE_REQUIRED, 'The model class')
            ->addOption('model-id', null, InputOption::VALUE_REQUIRED, 'The model id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paymentName = $input->getArgument('payment-name');
        $modelClass = $input->getOption('model-class');
        $modelId = $input->getOption('model-id');

        $storage = $this->getPayum()->getStorage($modelClass);
        if (false == $model = $storage->findModelById($modelId)) {
            throw new RuntimeException(sprintf(
                'Cannot find model with class %s and id %s.',
                $modelClass,
                $modelId
            ));
        }

        $status = new GetHumanStatus($model);
        $this->getPayum()->getPayment($paymentName)->execute($status);

        $output->writeln(sprintf('Status: %s', $status->getValue()));
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->getContainer()->get('payum');
    }
}
