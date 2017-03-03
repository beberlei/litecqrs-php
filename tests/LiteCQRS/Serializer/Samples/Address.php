<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 1.3.17
 * Time: 13:39
 */

namespace Lidskasila\Glow\Serializer;

class Address
{

	private $country;

	private $city;

	public function __construct($city, $country)
	{
		$this->city    = $city;
		$this->country = $country;
	}
}
