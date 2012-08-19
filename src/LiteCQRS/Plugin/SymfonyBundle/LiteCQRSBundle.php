<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;
use LiteCQRS\Plugin\JMSSerializer\AggregateRootHandlerFactory;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class LiteCQRSBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new HandlerPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

    public function configureSerializerExtension(JMSSerializerExtension $ext)
    {
        $ext->addHandlerFactory(new AggregateRootHandlerFactory());
    }
}

