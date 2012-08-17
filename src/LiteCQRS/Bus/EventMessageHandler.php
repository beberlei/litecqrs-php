<?php

namespace LiteCQRS\Bus;

use Exception;
use LiteCQRS\EventStore\IdentityMapInterface;

class EventMessageHandler implements MessageHandlerInterface
{
    private $messageBus;
    private $next;
    private $identityMap;

    public function __construct(MessageHandlerInterface $next, EventMessageBus $messageBus, IdentityMapInterface $identityMap = null)
    {
        $this->next        = $next;
        $this->messageBus  = $messageBus;
        $this->identityMap = $identityMap;
    }
    public function handle(MessageInterface $command)
    {
        try {
            $this->next->handle($command);
            $this->passEventsToStore();
            $this->messageBus->dispatchEvents();
        } catch(Exception $e) {
            $this->messageBus->clear();
            throw $e;
        }
    }

    protected function passEventsToStore()
    {
        if (!$this->identityMap) {
            return;
        }

        foreach ($this->identityMap->all() as $aggregateRoot) {
            $id = $this->identityMap->getAggregateId($aggregateRoot);
            foreach ($aggregateRoot->popAppliedEvents() as $event) {
                $header = $event->getMessageHeader();
                $header->aggregateType = get_class($aggregateRoot);
                $header->aggregateId   = $id;
                $header->setAggregate(null);

                $this->messageBus->publish($event);
            }
        }
    }
}

