<?php

namespace Lidskasila\Glow\Commanding;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class MemoryCommandHandlerLocatorTest extends TestCase
{

	/**
	 * @test
	 */
	public function it_throws_exception_when_no_handler_is_registered_for_command()
	{
		self::expectException(RuntimeException::class);
		self::expectExceptionMessage("No service registered for command type 'NoHandlerCommand'");

		$locator = new MemoryCommandHandlerLocator();
		$locator->getCommandHandler(new NoHandlerCommand());
	}
}


