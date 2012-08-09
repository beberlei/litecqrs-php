# LiteCQRS for PHP

Small convention based CQRS library for PHP (loosly based on [LiteCQRS for C#](https://github.com/danielwertheim/LiteCQRS)).

Conventions are:

* All public methods of a command handler class are mapped to Commands "Command Class Shortname" => "MethodName", for example "MyLib\DoSomethingCommand" => "doSomething($command)"
* Domain Events are applied on Entities/Aggregate Roots "Event Class Shortname" => "applyEventClassShortname", for example "MyLib\SomethingDoneEvent => "applySomethingDone($event)"
* Domain Events are applied to Event Handlers "Event Class Shortname" => "onEventClassShortname", for example "MyLib\SomethingDoneEvent" => "onSomethingDone($event)"
* As long as your events don't have listeners, you can also use the ``DomainObjectChanged`` Event. You can dynamically set the event name on it and exchange it with a real event implementation when it becomes necessary.

## Workflow

CQRS is an event driven architecture:

1. You push commands into a ``CommandBus``. Commands are simple objects created by you.
2. The ``CommandBus`` checks for a handler that can execute your command. Every command has exactly one handler.
3. The command handler changes state of the domain model. It does that creating events (that represent state change)
   and passing them to the ``AggregateRootInterface::apply()`` method of your domain objects.
4. When the command finishes, the command bus will check all objects in the identity map for events.
5. All events found will be passed to the ``EventStoreInterface``.
6. The EventStore can save these events to a persistent storage.
7. After storing all events, event handlers are triggered that listen to the domain events (Pub-Sub).

Command execution should be wrapped in a transaction for example. The event triggering is not part
of that transaction. If the command transaction fails, then the events are all dropped. No event listeners
will be triggered in this case.

## Example

You can stick yourself together a simple CQRS application by configuring a ``CommandBus` and an ``EventMessageBus``.

```php
<?php

namespace MyApp;

use LiteCQRS\BaseAggregateRoot;
use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Bus\InMemoryEventMessageBus;
use LiteCQRS\EventStore\SimpleIdentityMap;
use LiteCQRS\EventStore\InMemoryEventStore;
use LiteCQRS\Command;
use LiteCQRS\DomainObjectChanged;

// 1. Setup the Library with InMemory Handlers
$messageBus = new InMemoryEventMessageBus();
$eventStore = new InMemoryEventStore($messageBus);
$identityMap = new SimpleIdentityMap();
$commandBus = new DirectCommandBus($eventStore, $identityMap);

// 2. Register a command service and an event handler
$userService = new UserService();
$someEventHandler = new MyEventHandler();
$commandBus->register('MyApp\ChangeEmailCommand', $userService);
$messageBus->register($someEventHandler);

// 3. Invoke command!
$commandBus->handle(new ChangeEmailCommand(1234, 'kontakt@beberlei.de'));

// 4. Classes
class UserService
{
    public function changeEmail(ChangeEmailCommand $command)
    {
        $user = $this->findUserById($command->id);
        $user->changeEmail($command->email);
    }
}

class User extends BaseAggregateRoot
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
```

## Extension Points

You should implement your own ``CommandBus`` or extend the existing to wire the whole process together
exactly as you need it to work.
