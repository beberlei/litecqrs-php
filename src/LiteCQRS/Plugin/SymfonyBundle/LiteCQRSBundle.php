<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;
use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\JMSSerializerPass;
use LiteCQRS\Plugin\JMSSerializer\AggregateRootHandlerFactory;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class LiteCQRSBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new HandlerPass(), PassConfig::TYPE_AFTER_REMOVING);
        $container->addCompilerPass(new JMSSerializerPass());
    }
}

