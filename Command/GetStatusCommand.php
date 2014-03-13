<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Model\Identificator;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GetStatusCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('payum:get-status')
            ->setDescription('Allows to get a payment status.')
            ->addArgument('model-class', InputArgument::REQUIRED, 'The payment details id.')
            ->addArgument('model-id', InputArgument::REQUIRED, 'The payment details class.')
            ->addOption('payment-name', null, InputOption::VALUE_OPTIONAL, 'The payment name', null)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $modelId = $input->getArgument('model-id');
        $modelClass = $input->getArgument('model-class');
        $paymentName = $input->getOption('payment-name');

        $storage = $this->getPayum()->getStorageForClass($modelClass, $paymentName);

        $identifictor = new Identificator($modelId, $modelClass);
        if (false == $storage->findModelByIdentificator($identifictor)) {
            throw new \LogicException(sprintf('Model with class %s and id %s could not be found', $modelClass, $modelId));
        }

        $status = new BinaryMaskStatusRequest($identifictor);
        $this->getPayum()->getPayment($paymentName)->execute($status);

        if ($status->isSuccess()) {
            $output->writeln('success');
        } else if ($status->isPending()) {
            $output->writeln('pending');
        } else if ($status->isCanceled()) {
            $output->writeln('canceled');
        } else if ($status->isExpired()) {
            $output->writeln('expired');
        } else if ($status->isFailed()) {
            $output->writeln('failed');
        } else if ($status->isNew()) {
            $output->writeln('new');
        } else if ($status->isSuspended()) {
            $output->writeln('suspended');
        } else {
            $output->writeln('unknown');
        }
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->getContainer()->get('payum');
    }

}