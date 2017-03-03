<?php

namespace Lidskasila\Glow\Plugin\SymfonyBundle;

use Lidskasila\Glow\Commanding\Command;
use Lidskasila\Glow\Commanding\CommandHandlerLocator;
use Lidskasila\Glow\Eventing\EventHandlerLocator;
use Lidskasila\Glow\Eventing\EventName;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHandlerLocator implements EventHandlerLocator, CommandHandlerLocator
{

	private $container;

	private $eventHandlers   = [];

	private $commandHandlers = [];

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	public function getHandlersFor(EventName $eventName)
	{
		$eventName = strtolower($eventName);

		if (!isset($this->eventHandlers[$eventName])) {
			return [];
		}

		$eventHandlers = [];
		foreach ($this->eventHandlers[$eventName] as $id) {
			$eventHandlers[] = $this->container->get($id);
		}

		return $eventHandlers;
	}

	public function getCommandHandler(Command $command)
	{
		return $this->container->get($this->commandHandlers[get_class($command)]);
	}

	public function registerEventHandlers($eventHandlers)
	{
		$this->eventHandlers = $eventHandlers;
	}

	public function registerCommandHandlers($commandHandlers)
	{
		$this->commandHandlers = $commandHandlers;
	}
}

