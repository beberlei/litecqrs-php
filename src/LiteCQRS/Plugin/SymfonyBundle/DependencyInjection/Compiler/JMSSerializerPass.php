<?php

namespace LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class JMSSerializerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ( ! $container->has('serializer')) {
            return;
        }

        $definition = $container->getDefinition('jms_serializer.metadata.chain_driver');
        $arguments = $definition->getArguments();

        array_unshift($arguments[0], new Reference('litecqrs.serializer.metadata_driver'));

        $definition->setArguments($arguments);
    }
}
