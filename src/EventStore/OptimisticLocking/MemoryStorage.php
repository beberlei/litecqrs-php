<?php

namespace LidskaSila\Glow\EventStore\OptimisticLocking;

use LidskaSila\Glow\EventStore\ConcurrencyException;

class MemoryStorage implements Storage
{

	private $streamData = [];

	public function load($id)
	{
		if (isset($this->streamData[$id])) {
			return $this->streamData[$id];
		}

		return null;
	}

	public function store($id, $className, $eventData, $nextVersion, $currentVersion)
	{
		if (isset($this->streamData[$id]) && $this->streamData[$id]->getVersion() !== $currentVersion) {
			throw new ConcurrencyException();
		}

		$this->streamData[$id] = new StreamData($id, $className, $eventData, $nextVersion);
	}

	public function contains($id)
	{
		return isset($this->streamData[$id]);
	}
}
