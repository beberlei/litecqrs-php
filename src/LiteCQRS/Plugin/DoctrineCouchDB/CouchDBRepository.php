<?php

namespace LiteCQRS\Plugin\DoctrineCouchDB;

use LiteCQRS\DomainEventProviderRepositoryInterface;
use LiteCQRS\EventProviderInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CouchDBRepository implements DomainEventProviderRepositoryInterface
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

    public function add(EventProviderInterface $object)
    {
        $this->objectManager->persist($object);
    }

    public function remove(EventProviderInterface $object)
    {
        $this->objectManager->remove($object);
    }
}

