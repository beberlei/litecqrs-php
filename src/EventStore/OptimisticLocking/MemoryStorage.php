<?php

namespace LidskaSila\Glow\EventStore\OptimisticLocking;

use LidskaSila\Glow\EventStore\ConcurrencyException;

class MemoryStorage implements Storage
{

	private $streamData = [];

	/**
	 * @param string $id
	 *
	 * @return StreamData|null
	 */
	public function load(string $id)
	{
		if (isset($this->streamData[$id])) {
			return $this->streamData[$id];
		}

		return null;
	}

	public function store(string $id, string $className, array $newEventData, int $nextVersion, int $currentVersion = null): void
	{
		if (isset($this->streamData[$id]) && $this->streamData[$id]->getVersion() !== $currentVersion) {
			throw new ConcurrencyException();
		}
		if (!isset($this->streamData[$id])) {
			$this->streamData[$id] = $this->createNewStreamData($id, $className, $newEventData, $nextVersion);
		} else {
			$this->streamData[$id] = $this->mergeNewStreamData($id, $className, $newEventData, $nextVersion);
		}
	}

	public function contains(string $id): bool
	{
		return isset($this->streamData[$id]);
	}

	protected function createNewStreamData($id, $className, $newEventData, $nextVersion): StreamData
	{
		return new StreamData($id, $className, $newEventData, $nextVersion);
	}

	protected function mergeNewStreamData($id, $className, $newEventData, $nextVersion): StreamData
	{
		$allEventData = $this->streamData[$id]->getEventData();
		foreach ($newEventData as $newEventDataSingle) {
			$allEventData[] = $newEventDataSingle;
		}

		return $this->createNewStreamData($id, $className, $allEventData, $nextVersion);
	}
}
