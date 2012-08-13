<?php

namespace LiteCQRS\Plugin\Doctrine;

use LiteCQRS\EventStore\IdentityMapInterface;
use LiteCQRS\AggregateRootInterface;
use Doctrine\ORM\EntityManager;

class DoctrineIdentityMap implements IdentityMapInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function add(AggregateRootInterface $object)
    {
        $this->entityManager->persist($object);
    }

    public function all()
    {
        $aggregateRoots = array();
        $uow            = $this->entityManager->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $class => $entities) {
            foreach ($entities as $entity) {
                if (!($entity instanceof AggregateRootInterface)) {
                    break;
                }

                $aggregateRoots[] = $entity;
            }
        }

        return $aggregateRoots;
    }

    public function getAggregateId(AggregateRootInterface $object)
    {
        $class = $this->entityManager->getClassMetadata(get_class($object));

        if ($class->isIdentifierComposite) {
            return $class->getIdentifierValues($object);
        }

        return $class->getSingleIdReflectionProperty()->getValue($object);
    }
}

