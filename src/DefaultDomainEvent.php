<?php

namespace LidskaSila\Glow;

use LidskaSila\Glow\Eventing\EventName;
use LidskaSila\Glow\Exception\IdWasAlreadySetException;

abstract class DefaultDomainEvent implements DomainEvent
{

	/** @var Identity */
	private $id;

	public function __construct(array $data = null)
	{
		foreach ((array) $data as $key => $value) {
			$this->assertPropertyExists($key);

			$this->$key = $value;
		}
	}

	private function assertPropertyExists($name)
	{
		if (!property_exists($this, $name)) {
			$eventName = new EventName($this);
			throw new \RuntimeException('Property ' . $name . ' is not a valid property on event ' . $eventName);
		}
	}

	public function setAggregateId(Identity $id)
	{
		if ($this->id) {
			throw new IdWasAlreadySetException();
		}
		$this->id = $id;
	}

	public function getAggregateId()
	{
		return $this->id;
	}

	public function __get($name)
	{
		$this->assertPropertyExists($name);

		return $this->$name;
	}
}
