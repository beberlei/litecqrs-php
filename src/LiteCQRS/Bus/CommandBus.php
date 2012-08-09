<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;
use LiteCQRS\EventStore\EventStoreInterface;

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

    public function __construct(EventStoreInterface $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    abstract protected function getService($commandType);

    public function handle(Command $command)
    {
        $type    = get_class($command);
        $service = $this->getService($type);
        $method  = $this->getHandlerMethodName($command);

        $service = $this->wrapHandlerChain($service, $method);

        $this->eventStore->beginTransaction(); // clear exisiting events

        try {
            $service->$method($command);
            $this->eventStore->commit();

        } catch(\Exception $e) {
            $this->eventStore->rollback();

            throw $e;
        }
    }

    public function getHandlerMethodName($command)
    {
        $parts = explode("\\", get_class($command));
        return str_replace("Command", "", lcfirst(end($parts)));
    }

    protected function wrapHandlerChain($service, $method)
    {
        return $service;
    }
}

