<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB;

use LiteCQRS\AggregateRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CouchDBRepository implements AggregateRepositoryInterface
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

    public function add($object)
    {
        $this->objectManager->persist($object);
    }

    public function remove($object)
    {
        $this->objectManager->remove($object);
    }
}

