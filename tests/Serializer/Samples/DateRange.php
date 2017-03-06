<?php

namespace LidskaSila\Glow\Serializer;

class DateRange
{

	private $start;

	private $end;

	public function __construct(\DateTime $start, \DateTime $end)
	{
		$this->start = $start;
		$this->end   = $end;
	}
}
