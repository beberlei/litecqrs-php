<?php

namespace LiteCQRS;

/**
 * Very simple aggregate repository that you can
 * use for reusable behaviors, independent of the underlying
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
     * @return object
     */
    public function find($class, $id);

    /**
     * Add aggregate root to repository and schedule for persistence.
     *
     * @param object $object
     */
    public function add($object);

    /**
     * Schedule aggregate root for removal
     *
     * @param object $object
     */
    public function remove($object);
}

