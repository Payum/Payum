<?php
namespace Payum\Bundle\PayumBundle\Command;

use Payum\Core\Exception\RuntimeException;
use Payum\Core\Registry\RegistryInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateNotifyTokenCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('payum:security:create-notify-token')
            ->addArgument('gateway-name', InputArgument::REQUIRED, 'The gateway name associated with the token')
            ->addOption('model-class', null, InputOption::VALUE_OPTIONAL, 'The model class associated with the token')
            ->addOption('model-id', null, InputOption::VALUE_OPTIONAL, 'The model id associated with the token')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gatewayName = $input->getArgument('gateway-name');
        $modelClass = $input->getOption('model-class');
        $modelId = $input->getOption('model-id');
        $model = null;

        if  ($modelClass && $modelId) {
            if (false == $model = $this->getPayum()->getStorage($modelClass)->find($modelId)) {
                throw new RuntimeException(sprintf(
                    'Cannot find model with class %s and id %s.',
                    $modelClass,
                    $modelId
                ));
            }
        }

        $token = $this->getTokenFactory()->createNotifyToken($gatewayName, $model);

        $output->writeln(sprintf('Hash: <info>%s</info>', $token->getHash()));
        $output->writeln(sprintf('Url: <info>%s</info>', $token->getTargetUrl()));
        $output->writeln(sprintf('Details: <info>%s</info>', (string) $token->getDetails() ?: 'null'));
    }

    /**
     * @return GenericTokenFactoryInterface
     */
    protected function getTokenFactory()
    {
        return $this->getContainer()->get('payum.security.token_factory');
    }

    /**
     * @return RegistryInterface
     */
    protected function getPayum()
    {
        return $this->getContainer()->get('payum');
    }
} 