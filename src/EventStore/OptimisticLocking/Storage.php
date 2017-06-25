<?php

namespace LidskaSila\Glow\EventStore\OptimisticLocking;

interface Storage
{

	/**
	 * Load StreamData from the persistence storage layer.
	 *
	 * @param string $id
	 *
	 * @return StreamData|null
	 */
	public function load(string $id);

	/**
	 * Store event stream data in persistence storage layer.
	 *
	 * Requires a check on the current version in the actual database
	 * for optimistic locking purposes.
	 */
	public function store(string $id, string $className, array $newEventData, int $nextVersion, int $currentVersion = null): void;

	/**
	 * Check if the storage contains a stream data entry.
	 */
	public function contains(string $id): bool;
}
