<?php

namespace LiteCQRS;

/**
 * Very simple aggregate repository that you can
 * use for reusable behaviors, independent of the underyling
 * persistence storage or event sourcing
 */
interface AggregateRepositoryInterface
{
    /**
     * Find an aggregate root by class name and id
     *
     * @param string $class
     * @param mixed $id
     *
     * @return AggregateRootInterface
     */
    public function find($class, $id);

    /**
     * Add aggregate root to repository and schedule for persistence.
     *
     * @param AggregateRootInterface $object
     */
    public function add(AggregateRootInterface $object);

    /**
     * Schedule aggregate root for removal
     *
     * @param AggregateRootInterface $object
     */
    public function remove(AggregateRootInterface $object);
}

