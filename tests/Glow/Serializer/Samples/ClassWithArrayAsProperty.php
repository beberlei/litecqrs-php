<?php

namespace LidskaSila\Glow\Serializer;

class ClassWithArrayAsProperty
{

	private $country;

	private $detailWithArray;

	public function __construct($country, $detailWithArray = [])
	{
		$this->country         = $country;
		$this->detailWithArray = $detailWithArray;
	}
}
