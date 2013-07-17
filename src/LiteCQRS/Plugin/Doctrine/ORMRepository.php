<?php

namespace LiteCQRS\Plugin\Doctrine;

use LiteCQRS\DomainEventProviderRepositoryInterface;
use LiteCQRS\EventProviderInterface;
use Doctrine\ORM\EntityManager;

class ORMRepository implements DomainEventProviderRepositoryInterface
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function find($class, $id)
    {
        return $this->entityManager->find($class, $id);
    }

    public function add(EventProviderInterface $object)
    {
        $this->entityManager->persist($object);
    }

    public function remove(EventProviderInterface $object)
    {
        $this->entityManager->remove($object);
    }
}
