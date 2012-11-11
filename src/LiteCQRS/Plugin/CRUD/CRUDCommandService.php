<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\AggregateRepositoryInterface;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\DeleteResourceCommand;
use LiteCQRS\DefaultCommand;

use LiteCQRS\Plugin\CRUD\DomainAggregateResource;

/**
 * CRUD Command Service handles Create, Update and Delete Commands
 * on entities.
 */
class CRUDCommandService
{
    private $repository;

    public function __construct(AggregateRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create aggregate instance
     *
     * @param DefaultCommand $command
     *
     * @return DomainAggregateResource
     *
     * @throws \InvalidArgumentException instance of DomainAggregateResource
     */
    protected function createAggregate(DefaultCommand $command)
    {
        $aggregateClass = isset($command->aggregateClass)
            ? $command->aggregateClass
            : 'LiteCQRS\Plugin\CRUD\DomainAggregateResource';

        $aggregate = new $aggregateClass;

        if (!$aggregate instanceof DomainAggregateResource) {
            throw new \InvalidArgumentException;
        }

        return $aggregate;
    }

    public function createResource(CreateResourceCommand $command)
    {
        $object = $this->createAggregate($command);
        $object->setDomain(new $command->class);
        $object->create($command->data);

        $this->repository->add($object->getDomain());
    }

    public function updateResource(UpdateResourceCommand $command)
    {
        $domain = $this->repository->find($command->class, $command->id);
        $object = $this->createAggregate($command);
        $object->setDomain($domain);
        $object->update($command->data);
    }

    public function deleteResource(DeleteResourceCommand $command)
    {
        $domain = $this->repository->find($command->class, $command->id);
        $object = $this->createAggregate($command);
        $object->setDomain($domain);
        $object->remove();

        $this->repository->remove($object->getDomain());
    }
}

