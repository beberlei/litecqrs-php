<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;
use LiteCQRS\EventStore\EventStoreInterface;
use LiteCQRS\EventStore\IdentityMapInterface;
use LiteCQRS\EventStore\EventStoreHandler;

/**
 * Command Bus accepts commands and finds the appropriate
 * handler to execute it.
 *
 * Execution of commands is wrapped into a UnitOfWork
 * transaction of the event storage. Events are only
 * published, if the command execution was succesful.
 */
abstract class CommandBus
{
    private $eventStore;
    private $identityMap;
    private $proxyFactoy;

    private $commandStack = array();

    public function __construct(EventStoreInterface $eventStore, IdentityMapInterface $identityMap = null, $proxyFactory = null)
    {
        $this->eventStore   = $eventStore;
        $this->identityMap  = $identityMap;
        $this->proxyFactory = $proxyFactory ?: function($service) { return $service; };
    }

    /**
     * Given a Commmand Type (ClassName) return an instance of
     * the service that is handling this command.
     *
     * @param string $commandType A Command Class name
     * @return object
     */
    abstract protected function getService($commandType);

    public function handle(Command $command)
    {
        $this->commandStack[] = $command;

        if (count($this->commandStack) > 1) {
            return;
        }

        while ($command = array_shift($this->commandStack)) {
            $type    = get_class($command);
            $service = $this->getService($type);
            $handler = new CommandInvocationHandler($service);
            $handler = $this->proxyHandler($handler);

            try {
                $handler->handle($command);
            } catch(\Exception $e) {
                throw $e;
            }
        }
    }

    protected function proxyHandler($handler)
    {
        $proxyFactory = $this->proxyFactory;
        $handler = $proxyFactory($handler);

        return new EventStoreHandler($handler, $this->eventStore, $this->identityMap);
    }
}

