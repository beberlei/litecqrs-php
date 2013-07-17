<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\DomainEventProviderRepositoryInterface;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\DeleteResourceCommand;

/**
 * CRUD Command Service handles Create, Update and Delete Commands
 * on entities.
 */
class CRUDCommandService
{
    private $repository;

    public function __construct(DomainEventProviderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function createResource(CreateResourceCommand $command)
    {
        $object = new $command->class;
        $object->create($command->data);

        $this->repository->add($object);
    }

    public function updateResource(UpdateResourceCommand $command)
    {
        $object = $this->repository->find($command->class, $command->id);
        $object->update($command->data);
    }

    public function deleteResource(DeleteResourceCommand $command)
    {
        $object = $this->repository->find($command->class, $command->id);
        $object->remove();

        $this->repository->remove($object);
    }
}

