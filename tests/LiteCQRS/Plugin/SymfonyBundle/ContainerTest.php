<?php

namespace Lidskasila\Glow\Plugin\SymfonyBundle;

use Lidskasila\Glow\Commanding\CommandBus;
use Lidskasila\Glow\Eventing\EventMessageBus;
use Lidskasila\Glow\Plugin\SymfonyBundle\DependencyInjection\Compiler\HandlerPass;
use Lidskasila\Glow\Plugin\SymfonyBundle\DependencyInjection\GlowExtension;
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
		self::assertInstanceOf(EventMessageBus::class, $container->get('glow.event_message_bus'));
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
		$loader    = new GlowExtension();
		$container->registerExtension($loader);
		$container->set('logger', self::getMockBuilder('Monolog\Logger'));
		$loader->load([ [] ], $container);

		$container->addCompilerPass(new HandlerPass(), PassConfig::TYPE_AFTER_REMOVING);
		$container->compile();

		return $container;
	}
}

