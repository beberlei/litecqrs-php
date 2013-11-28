<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use LiteCQRS\Bus\EventHandlerLocator;
use LiteCQRS\Bus\EventName;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerHandlerLocator implements EventHandlerLocator
{
    private $container;
    private $services = array();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getHandlersFor(EventName $eventName)
    {
        $eventName = strtolower($eventName);

        if (!isset($this->services[$eventName])) {
            return array();
        }

        $services = array();
        foreach ($this->services[$eventName] as $id) {
            $services[] = $this->container->get($id);
        }

        return $services;
    }

    public function registerServices($services)
    {
        $this->services = $services;
    }
}

