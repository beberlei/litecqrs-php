<?php
namespace LiteCQRS;

use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Bus\InMemoryEventMessageBus;
use LiteCQRS\DomainObjectChanged;
use LiteCQRS\EventStore\InMemoryEventStore;

class CQRSTest extends \PHPUnit_Framework_TestCase
{
    public function testAggregateRootApplyEvents()
    {
        $user = new User();
        $user->changeEmail("foo@example.com");

        $events = $user->getAppliedEvents();
        $this->assertEquals(1, count($events));
        $this->assertEquals("foo@example.com", end($events)->email);
    }

    public function testInvalidEventThrowsException()
    {
        $this->setExpectedException("BadMethodCallException", "There is no event named 'applyInvalid' that can be applied to 'LiteCQRS\User'");

        $user = new User();
        $user->changeInvalidEventName();
    }

    public function testDirectCommandBus()
    {
        $command     = new ChangeEmailCommand('kontakt@beberlei.de');

        $userService = $this->getMock('UserService', array('changeEmail'));
        $userService->expects($this->once())->method('changeEmail')->with($this->equalTo($command));

        $direct = new DirectCommandBus();
        $direct->register('LiteCQRS\ChangeEmailCommand', $userService);

        $direct->handle($command);
    }

    public function testDirectCommandBusInvalidService()
    {
        $direct = new DirectCommandBus();

        $this->setExpectedException("RuntimeException", "No valid service given for command type 'ChangeEmailCommand'");
        $direct->register('ChangeEmailCommand', null);
    }

    public function testHandleUnregisteredCommand()
    {
        $command = new ChangeEmailCommand('kontakt@beberlei.de');
        $direct = new DirectCommandBus();

        $this->setExpectedException("RuntimeException", "No service registered for command type 'LiteCQRS\ChangeEmailCommand'");
        $direct->handle($command);
    }

    public function testHandleEventOnInMemoryEventMessageBus()
    {
        $event = new DomainObjectChanged("Foo", array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->handle($event);
    }

    public function testHandleEventOnInMemoryEventMessageBusThrowsExceptionIsSwallowed()
    {
        $event = new DomainObjectChanged("Foo", array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event))->will($this->throwException(new \Exception));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->handle($event);
    }

    public function testCommitEventsInMemoryEventStoreDelegatesToMessageBus()
    {
        $event = new DomainObjectChanged("Foo", array());

        $bus = $this->getMock('LiteCQRS\Bus\EventMessageBus');
        $bus->expects($this->once())->method('handle')->with($this->equalTo($event));

        $store = new InMemoryEventStore($bus);
        $store->add($event);
        $store->commit();
    }

    public function testCommitEventsOnlyTriggersEachEventOnce()
    {
        $event = new DomainObjectChanged("Foo", array());

        $bus = $this->getMock('LiteCQRS\Bus\EventMessageBus');
        $bus->expects($this->once())->method('handle')->with($this->equalTo($event));

        $store = new InMemoryEventStore($bus);
        $store->add($event);

        $store->commit();
        $store->commit();
    }

    public function testRollbackCommitEventsNotTriggers()
    {
        $event = new DomainObjectChanged("Foo", array());

        $bus = $this->getMock('LiteCQRS\Bus\EventMessageBus');
        $bus->expects($this->never())->method('handle');

        $store = new InMemoryEventStore($bus);
        $store->add($event);

        $store->rollback();
        $store->commit();
    }
}

class User extends BaseAggregateRoot
{
    private $id;
    private $email;

    public function getId()
    {
        return $this->id;
    }

    public function changeEmail($email)
    {
        $this->apply(new DomainObjectChanged("ChangeEmail", array("email" => $email)));
    }

    protected function applyChangeEmail($event)
    {
        $this->email = $event->email;
    }

    public function changeInvalidEventName()
    {
        $this->apply(new DomainObjectChanged("Invalid", array()));
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
