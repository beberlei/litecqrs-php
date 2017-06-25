<?php

namespace LidskaSila\Glow;

class SampleCreated extends DefaultDomainEvent
{

	/** @var Identity */
	public $sampleId;

	/** @var array */
	public $foo;

	public function __construct(Identity $sampleId, array $data = [])
	{
		parent::__construct($data);
		$this->sampleId = $sampleId;
	}
}
