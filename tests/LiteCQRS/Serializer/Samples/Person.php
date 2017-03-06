<?php

namespace LidskaSila\Glow\Serializer;

class Person
{

	private $name;

	private $address;

	public function __construct($name, Address $address)
	{
		$this->name    = $name;
		$this->address = $address;
	}
}
