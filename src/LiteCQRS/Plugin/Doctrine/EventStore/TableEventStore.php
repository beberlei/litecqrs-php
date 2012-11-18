<?php

namespace LiteCQRS\Plugin\Doctrine\EventStore;

use LiteCQRS\DomainEvent;
use LiteCQRS\EventStore\EventStoreInterface;
use LiteCQRS\EventStore\SerializerInterface;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

/**
 * Store events in a database table using Doctrine DBAL.
 */
class TableEventStore implements EventStoreInterface
{
    private $conn;
    private $table;
    private $serializer;

    public function __construct(Connection $conn, SerializerInterface $serializer, $table = 'litecqrs_events')
    {
        $this->conn       = $conn;
        $this->serializer = $serializer;
        $this->table      = $table;
    }

    public function store(DomainEvent $event)
    {
        $header = $event->getMessageHeader();

        $aggregateId = $header->aggregateId;

        $this->conn->insert($this->table, array(
            'event_id'       => $header->id,
            'aggregate_type' => $header->aggregateType,
            'aggregate_id'   => $aggregateId,
            'event'          => $event->getEventName(),
            'event_date'     => $header->date->format('Y-m-d H:i:s'),// looses microseconds precision
            'command_id'     => $header->commandId,
            'session_id'     => $header->sessionId,
            'data'           => $this->serializer->serialize($event, 'json'),
        ));
    }
}

