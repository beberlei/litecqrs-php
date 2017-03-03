<?php

namespace Lidskasila\Glow;

use RuntimeException;
use PHPUnit\Framework\TestCase;

class DefaultDomainEventTest extends TestCase
{

	public function testArrayToProperties()
	{
		$event = new TestEvent([ 'test' => 'value' ]);

		self::assertEquals('value', $event->test);
	}

	public function testWrongPropertyThrowsException()
	{
		self::expectException(RuntimeException::class);
		self::expectExceptionMessage('Property unknown is not a valid property on event Test');
		new TestEvent([ 'unknown' => 'value' ]);
	}
}


