<?php

namespace LidskaSila\Glow;

use Ramsey\Uuid\UuidInterface;

interface Repository
{

	/**
	 * @param string  $className
	 * @param UuidInterface    $uuid
	 * @param integer $expectedVersion
	 *
	 * @return AggregateRoot
	 */
	public function find($className, UuidInterface $uuid, $expectedVersion = null);

	/**
	 * @param AggregateRoot $object
	 *
	 * @return void
	 */
	public function save(AggregateRoot $object);
}
