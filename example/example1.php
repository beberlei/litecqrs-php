<?php

namespace MyApp;

require_once __DIR__ . "/../vendor/autoload.php";

use LiteCQRS\AggregateRoot;
use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Bus\InMemoryEventMessageBus;
use LiteCQRS\Bus\SimpleIdentityMap;
use LiteCQRS\Bus\EventMessageHandlerFactory;
use LiteCQRS\Command;
use LiteCQRS\DomainObjectChanged;

class UserService
{
    private $map;
    public function __construct(SimpleIdentityMap $map)
    {
        $this->map = $map;
    }

    public function findUserById($id)
    {
        $user = new User();
        $this->map->add($user);
        return $user;
    }

    public function changeEmail(ChangeEmailCommand $command)
    {
        $user = $this->findUserById($command->id);
        $user->changeEmail($command->email);
    }
}

class User extends AggregateRoot
{
    private $email;
    public function changeEmail($email)
    {
        $this->apply(new DomainObjectChanged("ChangeEmail", array("email" => $email)));
    }

    protected function applyChangeEmail($event)
    {
        $this->email = $event->email;
    }
}

class ChangeEmailCommand implements Command
{
    public $id;
    public $email;

    public function __construct($id, $email)
    {
        $this->id = $id;
        $this->email = $email;
    }
}

class MyEventHandler
{
    public function onChangeEmail(DomainObjectChanged $event)
    {
        echo "E-Mail changed: " . $event->email . "\n";
    }
}

// 1. Setup the Library with InMemory Handlers
$messageBus = new InMemoryEventMessageBus();
$identityMap = new SimpleIdentityMap();
$commandBus = new DirectCommandBus(array(
    new EventMessageHandlerFactory($messageBus, $identityMap)
));

// 2. Register a command service and an event handler
$userService = new UserService($identityMap);
$someEventHandler = new MyEventHandler();
$commandBus->register('MyApp\ChangeEmailCommand', $userService);
$messageBus->register($someEventHandler);

// 3. Invoke command!
$commandBus->handle(new ChangeEmailCommand(1234, 'kontakt@beberlei.de'));

// 4. Classes
