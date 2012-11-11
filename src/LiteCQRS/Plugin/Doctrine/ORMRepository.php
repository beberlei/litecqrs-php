<?php

namespace LiteCQRS\Plugin\Doctrine;

use LiteCQRS\AggregateRepositoryInterface;
use Doctrine\ORM\EntityManager;

class ORMRepository implements AggregateRepositoryInterface
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

    public function add($object)
    {
        $this->entityManager->persist($object);
    }

    public function remove($object)
    {
        $this->entityManager->remove($object);
    }
}

