<?php

namespace LiteCQRS\Bus;

class MemoryCommandHandlerLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_when_registered_service_is_no_object()
    {
        $this->setExpectedException("RuntimeException", "No valid service given for command type 'foo'");

        $locator = new MemoryCommandHandlerLocator();
        $locator->register('foo', 'not an object');
    }

    /**
     * @test
     */
    public function it_throws_exception_when_no_handler_is_registered_for_command()
    {
        $this->setExpectedException("RuntimeException", "No service registered for command type 'LiteCQRS\Bus\NoHandlerCommand'");

        $locator = new MemoryCommandHandlerLocator();
        $locator->getCommandHandler(new NoHandlerCommand());
    }
}

class NoHandlerCommand extends \LiteCQRS\DefaultCommand
{
}
