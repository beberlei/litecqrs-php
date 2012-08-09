<?php
namespace LiteCQRS;

use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\DomainObjectChanged;

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
