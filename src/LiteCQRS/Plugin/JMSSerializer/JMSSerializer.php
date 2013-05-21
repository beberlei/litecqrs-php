<?php
namespace LiteCQRS\Plugin\JMSSerializer;

use LiteCQRS\DomainEvent;
use LiteCQRS\EventStore\SerializerInterface;
use JMS\SerializerBundle\Serializer\SerializerInterface AS JMSSerializerInterface;

/**
 * Implementation of the Serializer interface using JMS Serializer.
 */
class JMSSerializer implements SerializerInterface
{
    /**
     * @var JMS\SerializerBundle\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @var AggregateRootHandler
     */
    private $aggregateHandler;

    public function __construct(JMSSerializerInterface $serializer, AggregateRootHandler $handler)
    {
        $this->serializer       = $serializer;
        $this->aggregateHandler = $handler;
    }

    public function serialize(DomainEvent $event, $format)
    {
        $this->aggregateHandler->enable();
        $data = $this->serializer->serialize($event, $format);
        $this->aggregateHandler->disable();

        return $data;
    }

    public function deserialize($eventClass, $data, $format)
    {
        $this->aggregateHandler->enable();
        $data = $this->serializer->deserialize($eventClass, $data, $format);
        $this->aggregateHandler->disable();

        return $data;
    }
}
