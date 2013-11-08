<?php

namespace LiteCQRS\Plugin\JMSSerializer;

use Metadata\Driver\DriverInterface;
use JMS\SerializerBundle\Metadata\ClassMetadata;

/**
 * Metadata Driver for JMS Serializer of LiteCQRS classes.
 *
 * We require no serialization of properties of all the LiteCQRS classes.
 */
class LiteCQRSMetadataDriver implements DriverInterface
{
    static private $handleClasses = array(
        'LiteCQRS\\DefaultDomainEvent' => true,
        'LiteCQRS\\DomainEventProvider' => true,
        'LiteCQRS\\AggregateRoot' => true,
    );

    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if ( ! isset(self::$handleClasses[$class->getName()])) {
            return null;
        }

        $classMetadata = new ClassMetadata($name = $class->getName());
        $classMetadata->fileResources[] = $class->getFilename();

        return $classMetadata;
    }
}
