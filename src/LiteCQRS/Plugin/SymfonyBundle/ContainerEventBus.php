<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use LiteCQRS\Bus\AbstractEventMessageBus;
use LiteCQRS\Bus\EventName;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerEventBus extends AbstractEventMessageBus
{
    private $container;
    private $services;

    public function __construct(ContainerInterface $container, array $proxyFactories = array())
    {
        $this->container = $container;
        parent::__construct($proxyFactories);
    }

    protected function getHandlers(EventName $eventName)
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

