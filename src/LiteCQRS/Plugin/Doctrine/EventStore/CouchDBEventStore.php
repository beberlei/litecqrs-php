<?php

namespace LiteCQRS\Plugin\Doctrine\EventStore;

use LiteCQRS\EventStore\EventStoreInterface;
use LiteCQRS\EventStore\SerializerInterface;
use LiteCQRS\DomainEvent;

use Doctrine\CouchDB\CouchDBClient;

/**
 * Event Store for CouchDB.
 */
class CouchDBEventStore implements EventStoreInterface
{
    /**
     * @var LiteCQRS\EventStore\SerializerInterface
     */
    private $serializer;

    /**
     * @var Doctrine\CouchDB\CouchDBClient
     */
    private $couch;

    public function __construct(CouchDBClient $couch, SerializerInterface $serializer)
    {
        $this->couch      = $couch;
        $this->serializer = $serializer;
    }

    public function store(DomainEvent $event)
    {
        $header = $event->getMessageHeader();
        $data   = array(
            'type'           => 'litecqrs_event',
            'aggregate_type' => $header->aggregateType,
            'aggregate_id'   => $header->aggregateId,
            'event'          => $event->getEventName(),
            "date"           => $header->date->format('Y-m-d H:i:s.u'),
            'command_id'     => $header->commandId,
            'session_id'     => $header->sessionId,
            "payload"        => json_decode($this->serializer->serialize($event, "json"))
        );

        $this->couch->putDocument($data, $header->id);
    }
}

