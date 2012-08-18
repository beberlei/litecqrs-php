<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;
use LiteCQRS\Plugin\SymfonyBundle\AggregateRootHandlerFactory;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;

class LiteCQRSBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new HandlerPass());
    }

    public function configureSerializerExtension(JMSSerializerExtension $ext)
    {
        $ext->addHandlerFactory(new AggregateRootHandlerFactory());
    }
}

