<?php

namespace LiteCQRS\Plugin\SymfonyBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class LiteCQRSExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration, $configs);
        var_dump($config);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['orm']) {
            $loader->load('orm.xml');
            $container->setAlias('litecqrs.identity_map', 'litecqrs.identity_map.orm');
            $container->setAlias('litecqrs.repository', 'litecqrs.repository.orm');
        }

        if ($config['jms_serializer']) {
            $loader->load('jms_serializer.xml');
        }

        if ($config['crud']) {
            $loader->load('crud.xml');
        }

        if ($config['monolog']) {
            $loader->load('monolog.xml');
        }

        if ($config['swift_mailer']) {
            $loader->load('swiftmailer.xml');
        }

        if ($config['dbal_event_store']) {
            $loader->load('dbal_event_store.xml');
        }
    }
}

