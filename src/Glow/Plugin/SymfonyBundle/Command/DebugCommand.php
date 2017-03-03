<?php

namespace Lidskasila\Glow\Plugin\SymfonyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class DebugCommand extends ContainerAwareCommand
{

	protected function configure()
	{
		$this
			->setName('lite-cqrs:debug')
			->setDescription('Display currently registered commands and events.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainerBuilder();

		$maxName        = strlen('Command-Handler Service');
		$maxId          = strlen('Command');
		$maxCommandType = strlen('Class');
		$commands       = [];

		foreach ($container->findTaggedServiceIds('lite_cqrs.command_handler') as $id => $attributes) {
			$definition = $container->findDefinition($id);
			$class      = $definition->getClass();

			$reflClass = new \ReflectionClass($class);
			foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
				if ($method->getNumberOfParameters() != 1) {
					continue;
				}

				/** @var \ReflectionParameter $commandParam */
				$commandParam = current($method->getParameters());

				if (!$commandParam->getClass()) {
					continue;
				}

				$commandClass = $commandParam->getClass();
				$commandType  = $commandClass->getName();

				$parts = explode('\\', $commandType);
				$name  = preg_replace('/Command/i', '', end($parts));

				if (strtolower($method->getName()) !== strtolower($name)) {
					continue;
				}

				$commands[$id][$commandType] = [ 'name' => $commandClass->getShortName(), 'id' => $id, 'class' => $class ];

				$maxName        = max(strlen($commandClass->getShortName()), $maxName);
				$maxId          = max(strlen($id), $maxId);
				$maxCommandType = max(strlen($commandType), $maxCommandType);
			}
		}

		$output->writeln('<info>COMMANDS</info>');
		$output->writeln('<info>========</info>');
		$output->writeln('');

		$format = '%-' . $maxId . 's %-' . $maxName . 's %s';

		// the title field needs extra space to make up for comment tags
		$format1 = '%-' . ($maxId + 19) . 's %-' . ($maxName + 19) . 's %s';
		$output->writeln(sprintf($format1, '<comment>Command-Handler Service</comment>', '<comment>Command</comment>', '<comment>Class</comment>'));

		foreach ($commands as $service => $serviceCommands) {
			foreach ($serviceCommands as $type => $command) {
				$output->writeln(sprintf($format, $service, $command['name'], $type));
			}
			$output->writeln('');
		}

		$events       = [];
		$maxName      = strlen('Event');
		$maxId        = strlen('Event-Handler Service');
		$maxEventName = strlen('Class');

		foreach ($container->findTaggedServiceIds('lite_cqrs.event_handler') as $id => $attributes) {
			$definition = $container->findDefinition($id);
			$class      = $definition->getClass();

			$reflClass = new \ReflectionClass($class);
			foreach ($reflClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
				if ($method->getNumberOfParameters() != 1) {
					continue;
				}

				$methodName = $method->getName();
				if (strpos($methodName, 'on') !== 0) {
					continue;
				}

				$eventName = (substr($methodName, 2));

				if (!isset($services[$eventName])) {
					$services[$eventName] = [];
				}

				$events[$id][] = [ 'eventName' => $eventName, 'id' => $id, 'class' => $class ];
				$maxName       = max(strlen($eventName), $maxName);
				$maxId         = max(strlen($id), $maxId);
				$maxEventName  = max(strlen($eventName), $maxEventName);
			}
		}

		$output->writeln('');
		$output->writeln('<info>EVENTS</info>');
		$output->writeln('<info>========</info>');
		$output->writeln('');

		$format = '%-' . $maxId . 's %-' . $maxEventName . 's %s';

		// the title field needs extra space to make up for comment tags
		$format1 = '%-' . ($maxId + 19) . 's %-' . ($maxEventName + 19) . 's %s';
		$output->writeln(sprintf($format1, '<comment>Event-Handler Service</comment>', '<comment>Event</comment>', '<comment>Class</comment>'));

		foreach ($events as $serviceId => $serviceEvents) {
			foreach ($serviceEvents as $event) {
				$output->writeln(sprintf($format, $event['id'], $event['eventName'], $event['class']));
			}
			$output->writeln('');
		}
	}

	/**
	 * Loads the ContainerBuilder from the cache.
	 *
	 * @return ContainerBuilder
	 */
	private function getContainerBuilder()
	{
		if (!$this->getApplication()->getKernel()->isDebug()) {
			throw new \LogicException(sprintf('Debug information about the container is only available in debug mode.'));
		}

		$cachedFile = $this->getContainer()->getParameter('debug.container.dump');
		if (!file_exists($cachedFile)) {
			throw new \LogicException(sprintf('Debug information about the container could not be found. Please clear the cache and try again.'));
		}

		$container = new ContainerBuilder();

		$loader = new XmlFileLoader($container, new FileLocator());
		$loader->load($cachedFile);

		return $container;
	}
}

