<?php

namespace LiteCQRS\Bus;

use Exception;
use LiteCQRS\EventStore\EventStoreInterface;

class EventMessageHandler implements MessageHandlerInterface
{
    private $messageBus;
    private $next;
    private $identityMap;
    private $eventStore;

    public function __construct(MessageHandlerInterface $next, EventMessageBus $messageBus, IdentityMapInterface $identityMap = null, EventStoreInterface $eventStore = null)
    {
        $this->next        = $next;
        $this->messageBus  = $messageBus;
        $this->identityMap = $identityMap;
        $this->eventStore  = $eventStore;
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
            foreach ($aggregateRoot->dequeueAppliedEvents() as $event) {
                $header = $event->getMessageHeader();
                $header->aggregateType = get_class($aggregateRoot);
                $header->aggregateId   = $id;
                $header->setAggregate(null);

                if ($this->eventStore) {
                    $this->eventStore->store($event);
                }
                $this->messageBus->publish($event);
            }
        }
    }
}

