<?php

namespace LiteCQRS\Plugin\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();

        $tb
            ->root('lite_cqrs')
                ->children()
                    ->booleanNode('monolog')->defaultTrue()->end()
                    ->booleanNode('swift_mailer')->defaultFalse()->end()
                    ->booleanNode('orm')->defaultFalse()->end()
                    ->booleannode('jms_serializer')->defaultFalse()->end()
                    ->booleanNode('dbal_event_store')->defaultFalse()->end()
                    ->booleanNode('couchdb_event_store')->defaultFalse()->end()
                    ->arrayNode('mongodb_event_store')
                        ->canBeUnset()
                        ->children()
                            ->scalarNode('database')->isRequired()->end()
                        ->end()
                    ->end()
                    ->booleanNode('couchdb_odm')->defaultFalse()->end()
                    ->booleanNode('crud')->defaultFalse()->end()
                ->end();

        return $tb;
    }
}
