<?php

namespace LidskaSila\Glow\Eventing;

use LidskaSila\Glow\DomainEvent;

class EventName
{

	private $event;

	private $name;

	public function __construct(DomainEvent $event)
	{
		$this->event = $event;
	}

	public function __toString()
	{
		if ($this->name === null) {
			$this->name = $this->parseName();
		}

		return $this->name;
	}

	private function parseName()
	{
		$class = get_class($this->event);

		if (substr($class, -5) === 'Event') {
			$class = substr($class, 0, -5);
		}

		if (strpos($class, '\\') === false) {
			return $class;
		}

		$parts = explode('\\', $class);

		return end($parts);
	}
}
