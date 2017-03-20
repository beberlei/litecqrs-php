<?php

namespace LidskaSila\Glow;

use Ramsey\Uuid\UuidInterface;

/**
 * Identity is Value object that server as identity in id of each AggregateRoot
 */
interface Identity
{

	public function getUuid(): UuidInterface;
}

