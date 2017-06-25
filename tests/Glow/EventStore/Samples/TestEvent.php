<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\DefaultDomainEvent;
use LidskaSila\Glow\Identity;

class TestEvent extends DefaultDomainEvent
{

	/** @var Identity */
	private $testId;

	public function __construct(Identity $testId, array $data = null)
	{
		parent::__construct($data);
		$this->testId = $testId;
	}

	/**
	 * @return Identity
	 */
	public function getTestId(): Identity
	{
		return $this->testId;
	}
}
