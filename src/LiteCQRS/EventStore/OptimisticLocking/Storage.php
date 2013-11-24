<?php

namespace LiteCQRS\EventStore\OptimisticLocking;

interface Storage
{
    /**
     * Load StreamData from the persistence storage layer.
     *
     * @param string $id
     * @return \LiteCQRS\EventStore\OptimisticLocking\StreamData
     */
    public function load($id);

    /**
     * Store event stream data in persistence storage layer.
     *
     * Requires a check on the current version in the actual database
     * for optimistic locking purposes.
     *
     * @param string $id
     * @param string $className
     * @param array $eventData
     * @param int $nextVersion
     * @param int $currentVersion
     *
     * @return void
     */
    public function store($id, $className, $eventData, $nextVersion, $currentVersion);

    /**
     * Check if the storage contains a stream data entry.
     *
     * @param string $id
     *
     * @return bool
     */
    public function contains($id);
}
