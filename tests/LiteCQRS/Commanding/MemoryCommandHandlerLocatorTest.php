<?php

namespace LiteCQRS\Commanding;

use PHPUnit\Framework\TestCase;

class MemoryCommandHandlerLocatorTest extends TestCase
{

	/**
	 * @test
	 */
	public function it_throws_exception_when_registered_service_is_no_object()
	{
		self::expectException("RuntimeException");
		self::expectExceptionMessage("No valid service given for command type 'foo'");

		$locator = new MemoryCommandHandlerLocator();
		$locator->register('foo', 'not an object');
	}

	/**
	 * @test
	 */
	public function it_throws_exception_when_no_handler_is_registered_for_command()
	{
		self::expectException("RuntimeException");
		self::expectExceptionMessage("No service registered for command type 'LiteCQRS\\Commanding\\NoHandlerCommand'");

		$locator = new MemoryCommandHandlerLocator();
		$locator->getCommandHandler(new NoHandlerCommand());
	}
}


