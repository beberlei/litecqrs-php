<?php

namespace LiteCQRS\Plugin\Doctrine;

use LiteCQRS\AggregateRepositoryInterface;
use LiteCQRS\AggregateRootInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DoctrineRepository implements AggregateRepositoryInterface
{
    private $objectManager;

    public function __construct(ObjectManager $manager)
    {
        $this->objectManager = $manager;
    }

    public function find($class, $id)
    {
        return $this->objectManager->find($class, $id);
    }

    public function add(AggregateRootInterface $object)
    {
        $this->objectManager->persist($object);
    }

    public function remove(AggregateRootInterface $object)
    {
        $this->objectManager->remove($object);
    }
}
