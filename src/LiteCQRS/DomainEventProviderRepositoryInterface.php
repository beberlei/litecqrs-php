<?php

namespace LiteCQRS;

/**
 * Very simple domain event provider repository that you can
 * use for reusable behaviors, independent of the underlying
 * persistence storage or event sourcing
 */
interface DomainEventProviderRepositoryInterface
{
    /**
     * Find an event provider object by class name and id
     *
     * @param string $class
     * @param mixed $id
     *
     * @return EventProviderInterface
     */
    public function find($class, $id);

    /**
     * Add event provider object to repository and schedule for persistence.
     *
     * @param EventProviderInterface $object
     */
    public function add(EventProviderInterface $object);

    /**
     * Schedule event provider object for removal
     *
     * @param EventProviderInterface $object
     */
    public function remove(EventProviderInterface $object);
}

