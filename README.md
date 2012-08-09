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

## Setup (Simple Case)

```php
<?php
// 1. Setup the Library with InMemory Handlers
$messageBus = new InMemoryEventMessageBus();
$eventStore = new InMemoryEventStore($messageBus);
$identityMap = new SimpleIdentityMap();
$commandBus = new DirectCommandBus($eventStore, $identityMap);

// 2. Register a command service and an event handler
$userService = new UserService($identityMap);
$commandBus->register('MyApp\ChangeEmailCommand', $userService);

$someEventHandler = new MyEventHandler();
$messageBus->register($someEventHandler);
```

## Usage

### To implement a Use Case of your application

1. Create a command object that recieves all the necessary input values. Use public properties to simplify.
2. Add a new command handler method on any of your services
3. Register the command handler to handle the given command.
4. Use the `DomainObjectChanged`` event to change the state of your domain objects.

That is all there is for simple use-cases. If you use the ``DomainObjectChanged`` event instead of writing
your own for every change you get away cheap.

If your command triggers events that listeners check for, you should:

1. Create a domain specific event class. Use public properties to simplify.
2. Create a event handler(s) or add method(s) to existing event handler(s).

While it seems "complicated" to create commands and events for every use-case. These objects are really
dumb and only contain public properties. Using your IDE or editor functionality you can easily template
them in no time.

### EventStore and IdentityMap

You have to implement a mechanism to fill the ```IdentityMapInterface``` passed
to the command bus. All aggregate root objects in this Identity Map will have their
Events stored and published through the EventStore + EventMessageBus. All other events
will be forgotten!

Example: The Doctrine ORM Plugin has an EventListener that synchronizes objects into the
CQRSList IdentityMap.

### Command/Event Handler Proxies

If you want to wrap the command/event handler in a transaction, you have to extend the ``CommandBus``
and pass a proxy factory closure/invokable object into the ```CommandBus```.

If you want to log all commands:

```php
<?php
use LiteCQRS\CommandHandler\CommandHandlerInterface;

class CommandLogger implements CommandHandlerInterface
{
    private $next;

    public function __construct(CommandHandler $next)
    {
        $this->next = $next;
    }

    public function handle(Command $command)
    {
        syslog(LOG_INFO, "Executing: " . get_class($command));
        $this->next->handle($command);
    }
}
```

And register:

```php
<?php
$proxyFactory = function($handler) {
    return new CommandLogger($handler);
};
$commandBus = new DirectCommandBus($eventStore, $identityMap, $proxyFactory);
```

## Example

See ``example/example1.php`` for a simple example.

## Extension Points

You should implement your own ``CommandBus`` or extend the existing to wire the whole process together
exactly as you need it to work.
