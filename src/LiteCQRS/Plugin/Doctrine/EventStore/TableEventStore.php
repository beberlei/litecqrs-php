<?php

namespace LiteCQRS\Plugin\Doctrine\EventStore;

use LiteCQRS\DomainEvent;
use LiteCQRS\EventStore\OrderedInMemoryEventStore;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\AggregateRootInterface;

use Doctrine\DBAL\Connection;

class TableEventStore extends OrderedInMemoryEventStore
{
    private $conn;
    private $table;

    public function __construct(Connection $conn, EventMessageBus $eventMessageBus, $table = 'events')
    {
        parent::__construct($eventMessageBus);

        $this->conn  = $conn;
        $this->table = $table;
    }

    public function commit()
    {
        $events = $this->sort($this->events);
        $this->events = array();

        foreach ($events as $event) {
            $header = $event->getMessageHeader();

            $this->conn->insert($this->table, array(
                'id'             => $header->id,
                'aggregate_type' => $header->aggregateType,
                'aggregate_id'   => $header->aggregateId,
                'event'          => $event->getEventName(),
                'date'           => $header->date->format('Y-m-d H:i:s'),// looses microseconds precision
                'command_id'     => $header->commandId,
                'session_id'     => $header->sessionId,
                'data'           => json_encode($event), // looses non public information and objects
            ));

            $this->eventMessageBus->handle($event);
        }
    }
}

