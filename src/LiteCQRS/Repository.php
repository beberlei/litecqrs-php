<?php

namespace LiteCQRS;

use Ramsey\Uuid\Uuid;

interface Repository
{

	/**
	 * @param string  $className
	 * @param Uuid    $uuid
	 * @param integer $expectedVersion
	 *
	 * @return AggregateRoot
	 */
	public function find($className, Uuid $uuid, $expectedVersion = null);

	/**
	 * @param AggregateRoot $object
	 *
	 * @return void
	 */
	public function save(AggregateRoot $object);
}
