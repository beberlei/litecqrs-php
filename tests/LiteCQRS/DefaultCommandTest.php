<?php

namespace LiteCQRS;

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
		self::expectException('RuntimeException');
		self::expectExceptionMessage('Property "unknown" is not a valid property on command "Test".');
		$cmd = new TestCommand([ 'unknown' => 'value' ]);
	}
}
