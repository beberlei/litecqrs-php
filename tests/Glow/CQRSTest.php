<?php

namespace LidskaSila\Glow;

use BadMethodCallException;
use LidskaSila\Glow\Commanding\CommandHandler;
use LidskaSila\Glow\Commanding\MemoryCommandHandlerLocator;
use LidskaSila\Glow\Commanding\SequentialCommandBus;
use LidskaSila\Glow\Eventing\MemoryEventHandlerLocator;
use LidskaSila\Glow\Eventing\SynchronousInProcessEventBus;
use PHPUnit\Framework\TestCase;

class CQRSTest extends TestCase
{

	public function testAggregateRootApplyEvents()
	{
		$user = new User(new UserId());
		$user->changeEmail('foo@example.com');

		$events = $user->pullDomainEvents();
		self::assertEquals(1, count($events));
		self::assertEquals('foo@example.com', end($events)->email);
	}

	public function testInvalidEventThrowsException()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('There is no event named "applyInvalid" that can be applied to "LidskaSila\Glow\User"');

		$user = new User(new UserId());
		$user->changeInvalidEventName();
	}

	public function testDirectCommandBus()
	{
		$command        = new ChangeEmailCommand('kontakt@beberlei.de');
		$commandHandler = new ChangeEmailCommandHandler();

		$bus = $this->newSequentialCommandBusWith($commandHandler);

		$bus->handle($command);

		self::assertTrue(true);
	}

	private function newSequentialCommandBusWith(CommandHandler $commandHandler)
	{
		$locator = new MemoryCommandHandlerLocator();
		$locator->register($commandHandler);

		return new SequentialCommandBus($locator);
	}

	public function testWhenSuccessfulCommandThenTriggersEventStoreCommit()
	{
		$userService = new ChangeEmailCommandHandler();
		$bus         = $this->newSequentialCommandBusWith($userService);

		$bus->handle(new ChangeEmailCommand('kontakt@beberlei.de'));
		self::assertTrue(true);
	}

	public function testHandleEventOnInMemoryEventMessageBus()
	{
		$event        = new FooEvent([]);
		$eventHandler = self::getMockBuilder('EventHandler')->setMethods([ 'onFoo' ])->getMock();
		$eventHandler->expects(self::once())->method('onFoo')->with(self::equalTo($event));

		$bus = $this->createInMemoryEventBusWith($eventHandler);
		$bus->publish($event);
	}

	private function createInMemoryEventBusWith($eventHandler)
	{
		$locator = new MemoryEventHandlerLocator();
		$locator->register($eventHandler);

		return new SynchronousInProcessEventBus($locator);
	}

	public function testDispatchEventsInOrder()
	{
		$event1 = new FooEvent([]);
		$event2 = new FooEvent([]);

		$eventHandler = self::getMockBuilder('EventHandler')->setMethods([ 'onFoo' ])->getMock();
		$eventHandler->expects(self::at(0))->method('onFoo')->with(self::equalTo($event1));
		$eventHandler->expects(self::at(1))->method('onFoo')->with(self::equalTo($event2));

		$bus = $this->createInMemoryEventBusWith($eventHandler);
		$bus->publish($event1);
		$bus->publish($event2);
	}

	public function testHandleEventOnInMemoryEventMessageBusThrowsExceptionIsSwallowed()
	{
		$event        = new FooEvent([]);
		$eventHandler = self::getMockBuilder('EventHandler')->setMethods([ 'onFoo' ])->getMock();
		$eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event))->will($this->throwException(new \Exception));

		$bus = $this->createInMemoryEventBusWith($eventHandler);
		$bus->publish($event);
	}
}
