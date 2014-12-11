<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Payment;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\AbstractStorage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DebugPaymentCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('payum:payment:debug')
            ->addArgument('payment-name', InputArgument::OPTIONAL, 'The payment name you want to get information about.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($paymentName = $input->getArgument('payment-name')) {
            $payments = array(
                $paymentName => $this->getPayum()->getPayment($paymentName)
            );
        } else {
            $payments = $this->getPayum()->getPayments();
        }

        $output->writeln(sprintf('Found <info>%d</info> payments', count($payments)));
        foreach ($this->getPayum()->getPayments() as $name => $payment) {
            $output->writeln('');
            $output->writeln(sprintf('%s (%s):', $name, get_class($payment)));

            if (false == $payment instanceof Payment) {
                continue;
            }

            $rp = new \ReflectionProperty($payment, 'actions');
            $rp->setAccessible(true);
            $actions = $rp->getValue($payment);
            $rp->setAccessible(false);

            $output->writeln("\t<info>Actions:</info>");
            foreach ($actions as $action) {
                $output->writeln(sprintf("\t%s", get_class($action)));
            }

            $rp = new \ReflectionProperty($payment, 'extensions');
            $rp->setAccessible(true);
            $collection = $rp->getValue($payment);
            $rp->setAccessible(false);

            $rp = new \ReflectionProperty($collection, 'extensions');
            $rp->setAccessible(true);
            $extensions = $rp->getValue($collection);
            $rp->setAccessible(false);

            $output->writeln("");
            $output->writeln("\t<info>Extensions:</info>");
            foreach ($extensions as $extension) {

                $output->writeln(sprintf("\t%s", get_class($extension)));

                if ($extension instanceof StorageExtension) {
                    $rp = new \ReflectionProperty($extension, 'storage');
                    $rp->setAccessible(true);
                    $storage = $rp->getValue($extension);
                    $rp->setAccessible(false);

                    $output->writeln(sprintf("\t\t%s", get_class($storage)));

                    if ($storage instanceof AbstractStorage) {
                        $rp = new \ReflectionProperty($storage, 'modelClass');
                        $rp->setAccessible(true);
                        $modelClass = $rp->getValue($storage);
                        $rp->setAccessible(false);

                        $output->writeln(sprintf("\t\t%s", $modelClass));
                    }
                }
            }

            $rp = new \ReflectionProperty($payment, 'apis');
            $rp->setAccessible(true);
            $apis = $rp->getValue($payment);
            $rp->setAccessible(false);

            $output->writeln("");
            $output->writeln("\t<info>Apis:</info>");
            foreach ($apis as $api) {
                $output->writeln(sprintf("\t%s", get_class($api)));
            }
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