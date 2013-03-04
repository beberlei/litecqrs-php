<?php

namespace LiteCQRS\Plugin\DoctrineMongoDB\EventStore;

use Doctrine\MongoDB\Connection;
use LiteCQRS\EventStore\SerializerInterface;
use LiteCQRS\DomainEvent;

class MongoDBEventStore implements \LiteCQRS\EventStore\EventStoreInterface
{
    private $connection;
    private $database;
    private $serializer;
    private $collection;

    public function __construct(Connection $connection, SerializerInterface $serializer, $database, $collection = 'litecqrs_events')
    {
        $this->connection = $connection;
        $this->database   = $database;
        $this->serializer = $serializer;
        $this->collection = $collection;
    }

    public function store(DomainEvent $event)
    {
        $header = $event->getMessageHeader();

        $data = array(
            'event_id'       => $header->id,
            'aggregate_type' => $header->aggregateType,
            'aggregate_id'   => $header->aggregateId,
            'event'          => $event->getEventName(),
            'event_date'     => $header->date,// looses microseconds precision
            'command_id'     => $header->commandId,
            'session_id'     => $header->sessionId,
            'data'           => json_decode($this->serializer->serialize($event, 'json')),
        );

        $this->connection->selectCollection($this->database, $this->collection)->insert($data);
    }
}
