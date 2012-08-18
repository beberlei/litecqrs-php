<?php

namespace LiteCQRS\Plugin\JMSSerializer;

use LiteCQRS\Bus\IdentityMapInterface;
use LiteCQRS\AggregateRepositoryInterface;

use JMS\SerializerBundle\Serializer\VisitorInterface;
use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Serializer\Handler\DeserializationHandlerInterface;
use JMS\SerializerBundle\Serializer\Handler\SerializationHandlerInterface;
use JMS\SerializerBundle\Serializer\GenericDeserializationVisitor;
use JMS\SerializerBundle\Serializer\GenericSerializationVisitor;

/**
 * Prevent aggregate root from being serialized into event data. Instead saving
 * a class name and id for fetching back with the repository.
 */
class AggregateRootHandler implements DeserializationHandlerInterface, SerializationHandlerInterface
{
    private $enabled = false;
    private $identityMap = array();

    public function __construct(IdentityMapInterface $identityMap, AggregateRepositoryInterface $repository)
    {
        $this->identityMap = $identityMap;
        $this->repository  = $repository;
    }

    public function enable()
    {
        $this->enabled = true;
    }

    public function disable()
    {
        $this->enabled = false;
    }

    public function serialize(VisitorInterface $visitor, $data, $type, &$handled)
    {
        if (!$this->enabled) {
            return false;
        }

        if (!class_exists($type) || !in_array('LiteCQRS\AggregateRootInterface', class_implements($type))) {
            return false;
        }

        $id = $this->identityMap->getAggregateId($data);

        if ($visitor instanceof GenericSerializationVisitor) {
            $handled = true;

            return array(
                'aggregate_type' => get_class($data),
                'aggregate_id'   => $id
            );
        } else {
            throw new \RuntimeException("Visitor is not supported.");
        }
    }

    public function deserialize(VisitorInterface $visitor, $data, $type, &$handled)
    {
        if (!$this->enabled) {
            return false;
        }

        if (!class_exists($type) || !in_array('LiteCQRS\AggregateRootInterface', class_implements($type))) {
            return false;
        }

        if (!($visitor instanceof GenericDeserializationVisitor)) {
            throw new \RuntimeException("Visitor not supported");
        }

        if (!isset($data['aggregate_type']) || !isset($data['aggregate_id'])) {
            throw new \RuntimeException("Cannot deserialize aggregate root");
        }

        $handled = true;
        return $this->repository->find($data['aggregate_type'], $data['aggregate_id']);
    }
}

