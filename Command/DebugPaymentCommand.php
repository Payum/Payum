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
use Symfony\Component\Console\Question\ChoiceQuestion;

class DebugPaymentCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('debug:payum:payment')
            ->setAliases(array(
                'payum:payment:debug',
            ))
            ->addArgument('payment-name', InputArgument::OPTIONAL, 'The payment name you want to get information about.')
            ->addOption('show-supports', null, InputOption::VALUE_NONE, 'Show what actions supports.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $payments = $this->getPayum()->getPayments();

        if ($paymentName = $input->getArgument('payment-name')) {
            $paymentName = $this->findProperPaymentName($input, $output, $payments, $paymentName);
            $payments = array(
                $paymentName => $this->getPayum()->getPayment($paymentName),
            );
        }

        $output->writeln('<info>Order of actions, apis, extensions matters</info>');

        $output->writeln(sprintf('Found <info>%d</info> payments', count($payments)));

        foreach ($payments as $name => $payment) {
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

                if ($input->getOption('show-supports')) {
                    $rm = new \ReflectionMethod($action, 'supports');
                    $output->write("\n\t".implode("\n\t", $this->getMethodCode($rm)));
                }
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

                    $output->writeln(sprintf("\t\t<info>Storage</info>: %s", get_class($storage)));

                    if ($storage instanceof AbstractStorage) {
                        $rp = new \ReflectionProperty($storage, 'modelClass');
                        $rp->setAccessible(true);
                        $modelClass = $rp->getValue($storage);
                        $rp->setAccessible(false);

                        $output->writeln(sprintf("\t\t<info>Model</info>: %s", $modelClass));
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
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return array
     */
    protected function getMethodCode(\ReflectionMethod $reflectionMethod)
    {
        $file = file($reflectionMethod->getFileName());

        $methodCodeLines = array();
        foreach (range($reflectionMethod->getStartLine(), $reflectionMethod->getEndLine() - 1) as $line) {
            $methodCodeLines[] = $file[$line];
        }

//        if (trim($methodCodeLines[count($methodCodeLines) - 1]) == '}') {
//            unset($methodCodeLines[count($methodCodeLines) - 1]);
//        }
//
//        if (trim($methodCodeLines[0]) == '{') {
//            unset($methodCodeLines[0]);
//        }

        return array_values($methodCodeLines);
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->getContainer()->get('payum');
    }

    private function findProperPaymentName(InputInterface $input, OutputInterface $output, $payments, $name)
    {
        $helperSet = $this->getHelperSet();
        if (!$helperSet->has('question') || isset($payments[$name]) || !$input->isInteractive()) {
            return $name;
        }

        $matchingPayments = $this->findPaymentsContaining($payments, $name);
        if (empty($matchingPayments)) {
            throw new \InvalidArgumentException(sprintf('No Payum payments found that match "%s".', $name));
        }
        $question = new ChoiceQuestion('Choose a number for more information on the payum payment', $matchingPayments);
        $question->setErrorMessage('Payum payment %s is invalid.');

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function findPaymentsContaining($payments, $name)
    {
        $threshold = 1e3;
        $foundPayments = array();

        foreach ($payments as $paymentName => $payment) {
            $lev = levenshtein($name, $paymentName);
            if ($lev <= strlen($name) / 3 || false !== strpos($paymentName, $name)) {
                $foundPayments[$paymentName] = isset($foundPayments[$paymentName]) ? $foundPayments[$payment] - $lev : $lev;
            }
        }

        $foundPayments = array_filter($foundPayments, function ($lev) use ($threshold) { return $lev < 2*$threshold; });
        asort($foundPayments);

        return array_keys($foundPayments);
    }
}
