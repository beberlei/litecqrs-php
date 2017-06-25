<?php

namespace LidskaSila\Glow;

use Ramsey\Uuid\UuidInterface;

interface Repository
{

	/**
	 * @param UuidInterface $uuid
	 * @param string        $className
	 * @param integer       $expectedVersion
	 *
	 * @return AggregateRoot
	 */
	public function find(UuidInterface $uuid, $className = null, $expectedVersion = null);

	/**
	 * @param AggregateRoot $object
	 *
	 * @return void
	 */
	public function save(AggregateRoot $object);
}
