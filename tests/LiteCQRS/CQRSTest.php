<?php
namespace LiteCQRS;

use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Bus\InMemoryEventMessageBus;
use LiteCQRS\DomainObjectChanged;
use LiteCQRS\EventStore\InMemoryEventStore;
use LiteCQRS\Bus\SimpleIdentityMap;
use LiteCQRS\Bus\EventMessageHandlerFactory;
use DateTime;

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

    public function testWhenSuccessfulCommandThenTriggersEventStoreCommit()
    {
        $messageBus = $this->getMock('LiteCQRS\Bus\EventMessageBus');
        $messageBus->expects($this->once())->method('dispatchEvents');

        $userService = $this->getMock('UserService', array('changeEmail'));
        $direct = new DirectCommandBus(array(new EventMessageHandlerFactory($messageBus)));
        $direct->register('LiteCQRS\ChangeEmailCommand', $userService);

        $direct->handle(new ChangeEmailCommand('kontakt@beberlei.de'));
    }

    public function testWhenFailingCommandThenTriggerEventStoreRollback()
    {
        $messageBus = $this->getMock('LiteCQRS\Bus\EventMessageBus');
        $messageBus->expects($this->once())->method('clear');

        $userService = $this->getMock('UserService', array('changeEmail'));
        $userService->expects($this->once())->method('changeEmail')->will($this->throwException(new \RuntimeException("DomainFail")));

        $direct = new DirectCommandBus(array(new EventMessageHandlerFactory($messageBus)));
        $direct->register('LiteCQRS\ChangeEmailCommand', $userService);

        $this->setExpectedException('LiteCQRS\Bus\CommandFailedStackException', 'During sequential execution 1 commands failed to execute: ChangeEmailCommand: DomainFail');
        $direct->handle(new ChangeEmailCommand('kontakt@beberlei.de'));
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

    public function testHandleWithIdentityMapWillPassEventsToStoreAfterSuccess()
    {
        $event = new DomainObjectChanged("Foo", array());
        $root = $this->getMock('LiteCQRS\AggregateRootInterface');
        $root->expects($this->once())->method('popAppliedEvents')->will($this->returnValue(array($event, $event)));

        $messageBus = $this->getMock('LiteCQRS\Bus\EventMessageBus');
        $messageBus->expects($this->exactly(2))->method('publish');

        $identityMap = $this->getMock('LiteCQRS\Bus\IdentityMapInterface');
        $identityMap->expects($this->once())->method('all')->will($this->returnValue(array($root)));

        $userService = $this->getMock('UserService', array('changeEmail'));
        $userService->expects($this->once())->method('changeEmail');

        $direct = new DirectCommandBus(array(new EventMessageHandlerFactory($messageBus, $identityMap)));
        $direct->register('LiteCQRS\ChangeEmailCommand', $userService);

        $direct->handle(new ChangeEmailCommand('kontakt@beberlei.de'));
    }

    public function testHandleEventOnInMemoryEventMessageBus()
    {
        $event = new DomainObjectChanged("Foo", array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event);
        $bus->dispatchEvents();
    }

    public function testDispatchEventsReSortedByDate()
    {
        $event1 = new DomainObjectChanged("Foo", array());
        $event2 = new DomainObjectChanged("Foo", array());

        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->at(0))->method('onFoo')->with($this->equalTo($event1));
        $eventHandler->expects($this->at(1))->method('onFoo')->with($this->equalTo($event2));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event2);
        $bus->publish($event1);
        $bus->dispatchEvents();
    }

    public function testDispatchEventsInOrder()
    {
        $event1 = new DomainObjectChanged("Foo", array());
        $event2 = new DomainObjectChanged("Foo", array());

        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->at(0))->method('onFoo')->with($this->equalTo($event1));
        $eventHandler->expects($this->at(1))->method('onFoo')->with($this->equalTo($event2));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event1);
        $bus->publish($event2);
        $bus->dispatchEvents();
    }

    public function testDispatchEventsInDifferentSeconds()
    {
        $event1 = new DomainObjectChanged("Foo", array());
        $event1->getMessageHeader()->date = new DateTime("2012-08-18 14:20:00");
        $event2 = new DomainObjectChanged("Foo", array());
        $event2->getMessageHeader()->date = new DateTime("2012-08-18 14:21:00");
        $event3 = new DomainObjectChanged("Foo", array());
        $event3->getMessageHeader()->date = new DateTime("2012-08-18 14:11:00");

        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->at(0))->method('onFoo')->with($this->equalTo($event3));
        $eventHandler->expects($this->at(1))->method('onFoo')->with($this->equalTo($event1));
        $eventHandler->expects($this->at(2))->method('onFoo')->with($this->equalTo($event2));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event1);
        $bus->publish($event2);
        $bus->publish($event3);
        $bus->dispatchEvents();
    }

    public function testPublishSameEventTwiceIsOnlyTriggeringOnce()
    {
        $event = new DomainObjectChanged("Foo", array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event);
        $bus->publish($event);
        $bus->dispatchEvents();
    }

    public function testPublishEventWithFailureTriggersFailureEventHandler()
    {
        $event = new DomainObjectChanged("Foo", array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo', 'onEventExecutionFailed'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event))->will($this->throwException(new \Exception));
        $eventHandler->expects($this->once())->method('onEventExecutionFailed');

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event);
        $bus->dispatchEvents();
    }

    public function testPublishSameEventAfterDispatchingAgainIsIgnored()
    {
        $event = new DomainObjectChanged("Foo", array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event);
        $bus->dispatchEvents();

        $bus->publish($event);
        $bus->dispatchEvents();
    }

    public function testHandleEventOnInMemoryEventMessageBusThrowsExceptionIsSwallowed()
    {
        $event = new DomainObjectChanged("Foo", array());
        $eventHandler = $this->getMock('EventHandler', array('onFoo'));
        $eventHandler->expects($this->once())->method('onFoo')->with($this->equalTo($event))->will($this->throwException(new \Exception));

        $bus = new InMemoryEventMessageBus();
        $bus->register($eventHandler);
        $bus->publish($event);
        $bus->dispatchEvents();
    }

    public function testEventsFromHistoryAreNotInTheAppliedEventsList()
    {
        $user = new User();
        $user->changeEmail("foo@bar.com");

        $events = $user->getAppliedEvents();

        $newUser = new User();
        $newUser->loadFromHistory($events);

        $this->assertEquals(0, count($newUser->getAppliedEvents()));
    }

    public function testPopAppliedEventsOnlyOnce()
    {
        $user = new User();
        $user->changeEmail("foo@bar.com");

        $events = $user->popAppliedEvents();
        $this->assertEquals(1, count($events));

        $events = $user->popAppliedEvents();
        $this->assertEquals(0, count($events));
    }

    public function testSimpleIdentityMapKeepsObjectUnique()
    {
        $ar = $this->getMock('LiteCQRS\AggregateRootInterface');

        $im = new SimpleIdentityMap();
        $im->add($ar);
        $im->add($ar);

        $this->assertEquals(array($ar), $im->all());
    }
}

class User extends AggregateRoot
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
