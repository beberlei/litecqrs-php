<?php

namespace LidskaSila\Glow;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class UuidIdentity implements Identity
{

	/** @var UuidInterface */
	protected $uuid;

	public function __construct(UuidInterface $uuid = null)
	{
		if (!$uuid) {
			$uuid = Uuid::uuid4();
		}
		$this->uuid = $uuid;
	}

	public function getUuid(): UuidInterface
	{
		return $this->uuid;
	}

	public function __toString()
	{
		return (string) $this->uuid;
	}
}

