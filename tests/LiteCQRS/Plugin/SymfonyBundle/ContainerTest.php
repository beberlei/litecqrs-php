<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;

use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\LiteCQRSExtension;
use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testContainer()
    {
        $container = $this->createTestContainer();

        $this->assertInstanceOf('LiteCQRS\Bus\CommandBus',      $container->get('command_bus'));
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
        $container->set('logger', $this->getMock('Monolog\Logger'));
        $container->set('swiftmailer.transport', $this->getMock('Swift_Transport_SpoolTransport', array(), array(), '', false));
        $container->set('swiftmailer.transport.real', $this->getMock('Swift_Transport', array(), array(), '', false));
        $container->set('form.factory', $this->getMock('Symfony\Component\Form\FormFactoryInterface'));
        $loader->load(array(array(
            "swift_mailer"     => true,
        )), $container);

        $container->getCompilerPassConfig()->setAfterRemovingPasses(array(new HandlerPass()));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}

