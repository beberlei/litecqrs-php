<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use LiteCQRS\Commanding\CommandBus;
use LiteCQRS\Eventing\EventMessageBus;
use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;
use LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\LiteCQRSExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ContainerTest extends TestCase
{

	public function testContainer()
	{
		$container = $this->createTestContainer();

		self::assertInstanceOf(CommandBus::class, $container->get('command_bus'));
		self::assertInstanceOf(EventMessageBus::class, $container->get('litecqrs.event_message_bus'));
	}

	public function createTestContainer()
	{
		$container = new ContainerBuilder(new ParameterBag([
			'kernel.debug'       => false,
			'kernel.bundles'     => [],
			'kernel.cache_dir'   => sys_get_temp_dir(),
			'kernel.environment' => 'test',
			'kernel.root_dir'    => __DIR__ . '/../../../../', // src dir
		]));
		$loader    = new LiteCQRSExtension();
		$container->registerExtension($loader);
		$container->set('logger', self::getMockBuilder('Monolog\Logger'));
		$loader->load([ [] ], $container);

		$container->addCompilerPass(new HandlerPass(), PassConfig::TYPE_AFTER_REMOVING);
		$container->compile();

		return $container;
	}
}

