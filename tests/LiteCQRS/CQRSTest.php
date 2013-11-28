<?php
namespace LiteCQRS;

use LiteCQRS\Eventing\SynchronousInProcessEventBus;
use LiteCQRS\Eventing\MemoryEventHandlerLocator;
use LiteCQRS\Commanding\SequentialCommandBus;
use LiteCQRS\Commanding\MemoryCommandHandlerLocator;

use DateTime;

use Rhumsaa\Uuid\Uuid;

class CQRSTest extends \PHPUnit_Framework_TestCase
{
    public function testAggregateRootApplyEvents()
    {
        $user = new User(Uuid::uuid4());
        $user->changeEmail("foo@example.com");

        $events = $user->pullDomainEvents();
        $this->assertEquals(1, count($events));
        $this->assertEquals("foo@example.com", end($events)->email);
    }

    public function testInvalidEventThrowsException()
    {
        $this->setExpectedException("BadMethodCallException", "There is no event named 'applyInvalid' that can be applied to 'LiteCQRS\User'");

        $user = new User(Uuid::uuid4());
        $user->changeInvalidEventName();
    }

    public function testDirectCommandBus()
    {
        $command     = new ChangeEmailCommand('kontakt@beberlei.de');

        $userService = $this->getMock('UserService', array('changeEmail'));
        $userService->expects($this->once())->method('changeEmail')->with($this->equalTo($command));

        $bus = $this->newSequentialCommandBusWith('LiteCQRS\ChangeEmailCommand', $userService);

        $bus->handle($command);
    }

    private function newSequentialCommandBusWith($commandType, $service)
    {
        $locator = new MemoryCommandHandlerLocator();
        $locator->register($commandType, $service);

        return new SequentialCommandBus($locator);
    }

    public function testWhenSuccessfulCommandThenTriggersEventStoreCommit()
    {
        $userService = $this->getMock('UserService', array('changeEmail'));
        $bus = $this->newSequentialCommandBusWith('LiteCQRS\ChangeEmailCommand', $userService);

        $bus->handle(new ChangeEmailCommand('kontakt@beberlei.de'));
    }

    public function testHandleEventOnInMemoryEventMessageBus()
    {
        $event = new FooEvent(array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event));

        $bus = $this->createInMemoryEventBusWith($eventHandler);
        $bus->publish($event);
    }

    public function testDispatchEventsInOrder()
    {
        $event1 = new FooEvent(array());
        $event2 = new FooEvent(array());

        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->at(0))->method('onFoo')->with($this->equalTo($event1));
        $eventHandler->expects($this->at(1))->method('onFoo')->with($this->equalTo($event2));

        $bus = $this->createInMemoryEventBusWith($eventHandler);
        $bus->publish($event1);
        $bus->publish($event2);
    }

    public function testHandleEventOnInMemoryEventMessageBusThrowsExceptionIsSwallowed()
    {
        $event = new FooEvent(array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event))->will($this->throwException(new \Exception));

        $bus = $this->createInMemoryEventBusWith($eventHandler);
        $bus->publish($event);
    }

    private function createInMemoryEventBusWith($eventHandler)
    {
        $locator = new MemoryEventHandlerLocator();
        $locator->register($eventHandler);

        return new SynchronousInProcessEventBus($locator);
    }
}

class User extends AggregateRoot
{
    private $email;

    public function __construct(Uuid $uuid)
    {
        $this->setId($uuid);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function changeEmail($email)
    {
        $this->apply(new ChangeEmailEvent(array("email" => $email)));
    }

    protected function applyChangeEmail($event)
    {
        $this->email = $event->email;
    }

    public function changeInvalidEventName()
    {
        $this->apply(new InvalidEvent(array()));
    }
}

class ChangeEmailCommand implements Command
{
    public $email;

    public function __construct($email)
    {
        $this->email = $email;
    }
}

class InvalidEvent extends DefaultDomainEvent
{
}

class ChangeEmailEvent extends DefaultDomainEvent
{
    public $email;
}

class FooEvent extends DefaultDomainEvent
{
}
