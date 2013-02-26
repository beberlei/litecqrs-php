<?php

namespace LiteCQRS\Bus;

use Exception;
use LiteCQRS\EventStore\EventStoreInterface;

class EventMessageHandler implements MessageHandlerInterface
{
    private $messageBus;
    private $next;
    private $queue;
    private $eventStore;

    public function __construct(MessageHandlerInterface $next, EventMessageBus $messageBus, EventQueue $queue = null, EventStoreInterface $eventStore = null)
    {
        $this->next        = $next;
        $this->messageBus  = $messageBus;
        $this->queue = $queue;
        $this->eventStore  = $eventStore;
    }

    public function handle($command)
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
        if (!$this->queue) {
            return;
        }

        foreach ($this->queue->dequeueAllEvents() as $event) {
            if ($this->eventStore) {
                $this->eventStore->store($event);
            }

            $this->messageBus->publish($event);
        }
    }
}

