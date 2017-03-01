<?php

namespace LiteCQRS\Plugin\SymfonyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class HandlerPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container)
	{
		$this->registerCommandHandlers($container);
		$this->registerEventHandlers($container);
	}

	private function registerCommandHandlers($container)
	{
		$services = [];
		foreach ($container->findTaggedServiceIds('lite_cqrs.command_handler') as $id => $attributes) {
			$definition = $container->findDefinition($id);
			$class      = $definition->getClass();

			$reflClass = new \ReflectionClass($class);

			foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
				// skip events
				if (strpos($method->getName(), "on") === 0) {
					continue;
				}

				if ($method->getNumberOfParameters() != 1) {
					continue;
				}

				$commandParam = current($method->getParameters());

				if (!$commandParam->getClass()) {
					continue;
				}

				$commandClass = $commandParam->getClass();
				$commandName  = strtolower(str_replace("Command", "", $commandClass->getShortName()));

				// skip methods where the command class name does not match the method name
				if ($commandName !== strtolower($method->getName())) {
					continue;
				}

				$services[$commandClass->getName()] = $id;
			}
		}

		$locatorDefinition = $container->findDefinition('litecqrs.container_handler_locator');
		$locatorDefinition->addMethodCall('registerCommandHandlers', [ $services ]);
	}

	private function registerEventHandlers($container)
	{
		$services = [];
		foreach ($container->findTaggedServiceIds('lite_cqrs.event_handler') as $id => $attributes) {
			$definition = $container->findDefinition($id);
			$class      = $definition->getClass();

			$reflClass = new \ReflectionClass($class);
			foreach ($reflClass->getMethods() as $method) {
				if ($method->getNumberOfParameters() != 1) {
					continue;
				}

				$methodName = $method->getName();
				if (strpos($methodName, "on") !== 0) {
					continue;
				}

				$eventName = strtolower(substr($methodName, 2));

				if (!isset($services[$eventName])) {
					$services[$eventName] = [];
				}

				$services[$eventName][] = $id;
			}
		}

		$locatorDefinition = $container->findDefinition('litecqrs.container_handler_locator');
		$locatorDefinition->addMethodCall('registerEventHandlers', [ $services ]);
	}
}

