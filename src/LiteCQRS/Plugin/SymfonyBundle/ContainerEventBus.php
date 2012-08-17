<?php

namespace LiteCQRS\Plugin\SymfonyBundle;

use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\DomainEvent;
use LiteCQRS\Bus\EventInvocationHandler;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Exception;

class ContainerEventBus implements EventMessageBus
{
    private $container;
    private $services;

    public function __construct(ContainerInterface $container, array $proxyFactories = array())
    {
        $this->container = $container;
        parent::__construct($proxyFactories);
    }

    protected function getHandlers($eventName)
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

