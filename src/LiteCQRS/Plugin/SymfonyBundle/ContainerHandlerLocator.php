<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use LiteCQRS\Bus\EventHandlerLocator;
use LiteCQRS\Commanding\CommandHandlerLocator;
use LiteCQRS\Bus\EventName;
use LiteCQRS\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHandlerLocator implements EventHandlerLocator, CommandHandlerLocator
{
    private $container;
    private $eventHandlers = array();
    private $commandHandlers = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getHandlersFor(EventName $eventName)
    {
        $eventName = strtolower($eventName);

        if (!isset($this->eventHandlers[$eventName])) {
            return array();
        }

        $eventHandlers = array();
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

