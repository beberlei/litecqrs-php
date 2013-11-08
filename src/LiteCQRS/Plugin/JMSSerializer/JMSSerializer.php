<?php
namespace LiteCQRS\Plugin\JMSSerializer;

use LiteCQRS\DomainEvent;
use LiteCQRS\EventStore\SerializerInterface;

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
     * Using duck-typing because of BC-break.
     *
     * @param JMS\Serializer\SerializerInterface|JMS\SerializerBundle\SerializerInterface $serializer
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    public function serialize(DomainEvent $event, $format)
    {
        return $this->serializer->serialize($event, $format);
    }

    public function deserialize($eventClass, $data, $format)
    {
        return $this->serializer->deserialize($eventClass, $data, $format);
    }
}

