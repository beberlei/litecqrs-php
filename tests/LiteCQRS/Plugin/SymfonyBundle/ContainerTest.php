<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\LiteCQRSExtension;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $container = $this->createTestContainer();

        $this->assertInstanceOf('LiteCQRS\Bus\CommandBus', $container->get('command_bus'));
        $this->assertInstanceOf('LiteCQRS\Bus\EventMessageBus', $container->get('litecqrs.event_message_bus'));
    }

    public function createTestContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => false,
            'kernel.bundles' => array(),
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir' => __DIR__.'/../../../../' // src dir
        )));
        $loader = new LiteCQRSExtension();
        $container->registerExtension($loader);
        $loader->load(array(array()), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}

