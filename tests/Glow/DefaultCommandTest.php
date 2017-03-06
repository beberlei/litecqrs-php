<?php

namespace LidskaSila\Glow;

use RuntimeException;
use PHPUnit\Framework\TestCase;

class DefaultCommandTest extends TestCase
{

	public function testCreateArrayMapsToPublicProperties()
	{
		$cmd = new TestCommand([ 'test' => 'value' ]);

		self::assertEquals('value', $cmd->test);
	}

	public function testCreateThrowsExceptionWhenUnknownPropertySet()
	{
		self::expectException(RuntimeException::class);
		self::expectExceptionMessage('Property "unknown" is not a valid property on command "TestCommand".');
		new TestCommand([ 'unknown' => 'value' ]);
	}
}
