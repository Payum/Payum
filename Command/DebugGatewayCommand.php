<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Storage\AbstractStorage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class DebugGatewayCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('debug:payum:gateway')
            ->setAliases(array(
                'payum:gateway:debug',
            ))
            ->addArgument('gateway-name', InputArgument::OPTIONAL, 'The gateway name you want to get information about.')
            ->addOption('show-supports', null, InputOption::VALUE_NONE, 'Show what actions supports.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gateways = $this->getPayum()->getGateways();

        if ($gatewayName = $input->getArgument('gateway-name')) {
            $gatewayName = $this->findProperGatewayName($input, $output, $gateways, $gatewayName);
            $gateways = array(
                $gatewayName => $this->getPayum()->getGateway($gatewayName),
            );
        }

        $output->writeln('<info>Order of actions, apis, extensions matters</info>');

        $output->writeln(sprintf('Found <info>%d</info> gateways', count($gateways)));

        foreach ($gateways as $name => $gateway) {
            $output->writeln('');
            $output->writeln(sprintf('%s (%s):', $name, get_class($gateway)));

            if (false == $gateway instanceof Gateway) {
                continue;
            }

            $rp = new \ReflectionProperty($gateway, 'actions');
            $rp->setAccessible(true);
            $actions = $rp->getValue($gateway);
            $rp->setAccessible(false);

            $output->writeln("\t<info>Actions:</info>");
            foreach ($actions as $action) {
                $output->writeln(sprintf("\t%s", get_class($action)));

                if ($input->getOption('show-supports')) {
                    $rm = new \ReflectionMethod($action, 'supports');
                    $output->write("\n\t".implode("\n\t", $this->getMethodCode($rm)));
                }
            }

            $rp = new \ReflectionProperty($gateway, 'extensions');
            $rp->setAccessible(true);
            $collection = $rp->getValue($gateway);
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

            $rp = new \ReflectionProperty($gateway, 'apis');
            $rp->setAccessible(true);
            $apis = $rp->getValue($gateway);
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

        return array_values($methodCodeLines);
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->getContainer()->get('payum');
    }

    private function findProperGatewayName(InputInterface $input, OutputInterface $output, $gateways, $name)
    {
        $helperSet = $this->getHelperSet();
        if (!$helperSet->has('question') || isset($gateways[$name]) || !$input->isInteractive()) {
            return $name;
        }

        $matchingGateways = $this->findGatewaysContaining($gateways, $name);
        if (empty($matchingGateways)) {
            throw new \InvalidArgumentException(sprintf('No Payum gateways found that match "%s".', $name));
        }
        $question = new ChoiceQuestion('Choose a number for more information on the payum gateway', $matchingGateways);
        $question->setErrorMessage('Payum gateway %s is invalid.');

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function findGatewaysContaining($gateways, $name)
    {
        $threshold = 1e3;
        $foundGateways = array();

        foreach ($gateways as $gatewayName => $gateway) {
            $lev = levenshtein($name, $gatewayName);
            if ($lev <= strlen($name) / 3 || false !== strpos($gatewayName, $name)) {
                $foundGateways[$gatewayName] = isset($foundGateways[$gatewayName]) ? $foundGateways[$gateway] - $lev : $lev;
            }
        }

        $foundGateways = array_filter($foundGateways, function ($lev) use ($threshold) { return $lev < 2*$threshold; });
        asort($foundGateways);

        return array_keys($foundGateways);
    }
}
