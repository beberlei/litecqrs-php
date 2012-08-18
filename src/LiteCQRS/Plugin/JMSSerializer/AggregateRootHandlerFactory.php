<?php

namespace LiteCQRS\Plugin\JMSSerializer;

use JMS\SerializerBundle\DependencyInjection\HandlerFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AggregateRootHandlerFactory implements HandlerFactoryInterface
{
    public function getConfigKey()
    {
        return 'litecqrs';
    }

    public function getType(array $config)
    {
        return self::TYPE_SERIALIZATION | self::TYPE_DESERIALIZATION;
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        // nothing so far
    }

    public function getHandlerId(ContainerBuilder $container, array $config)
    {
        return 'litecqrs.serializer.aggregate_root_handler';
    }
}

