<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB;

use LiteCQRS\Bus\IdentityMapInterface;
use LiteCQRS\EventProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CouchDBIdentityMap implements IdentityMapInterface
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function add(EventProviderInterface $object)
    {
        $this->objectManager->persist($object);
    }

    public function all()
    {
        $aggregateRoots = array();
        $uow = $this->objectManager->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $entity) {
            if (!($entity instanceof EventProviderInterface)) {
                break;
            }

            $aggregateRoots[] = $entity;
        }

        return $aggregateRoots;
    }

    public function getAggregateId(EventProviderInterface $object)
    {
        $class = $this->objectManager->getClassMetadata(get_class($object));

        return $class->getIdentifierValue($object);
    }
}

