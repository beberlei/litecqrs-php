<?php

namespace LiteCQRS\Tests\Bus;

use \LiteCQRS\Command;
use \LiteCQRS\Bus\CommandBus;
use \LiteCQRS\Bus\DirectCommandBus;

class SequentialCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \LiteCQRS\Exception\CommandFailedStackException
     */
    public function subcommandExceptionShouldBeThrown()
    {
        $command = new OuterCommand();

        $direct = new DirectCommandBus();
        $serviceWhichCallsAnotherService = new ServiceWhichCallsAnotherService($direct);
        $anotherServiceWhichThrowsAnException = new AnotherServiceWhichThrowsAnException();

        $direct->register('LiteCQRS\Tests\Bus\OuterCommand', $serviceWhichCallsAnotherService);
        $direct->register('LiteCQRS\Tests\Bus\InnerCommand', $anotherServiceWhichThrowsAnException);

        $direct->handle($command);
    }
}

class OuterCommand implements Command
{
}

class InnerCommand implements Command
{
}

class ServiceWhichCallsAnotherService
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function outer(OuterCommand $outer)
    {
        $this->commandBus->handle(new InnerCommand());
    }
}

class AnotherServiceWhichThrowsAnException
{
    /**
     * @param InnerCommand $inner
     * @throws \Exception
     */
    public function inner(InnerCommand $inner)
    {
        throw new \Exception('Handling of innerCommand failed for some reason.');
    }
}