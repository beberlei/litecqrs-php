# LiteCQRS for PHP

Small convention based CQRS library for PHP (loosly based on [LiteCQRS for C#](https://github.com/danielwertheim/LiteCQRS)).

## Conventions

* All public methods of a command handler class are mapped to Commands "Command Class Shortname" => "MethodName"
* Domain Events are applied on Entities/Aggregate Roots "Event Class Shortname" => "applyEventClassShortname"
* Domain Events are applied to Event Handlers "Event Class Shortname" => "onEventClassShortname"
* You can use the ``DomainObjectChanged`` Event to avoid creating lots of event classes. You can dynamically set the event name on it and exchange it with a real event implementation when it becomes necessary.
* Otherwise you can extend the ``DefaultDomainEvent`` which has a constructor that maps its array input to properties and throws an exception if an unknown property is passed.
* There is also a ``DefaultCommand`` with the same semantics as ``DefaultDomainEvent``

Examples:

* ``HelloWorld\GreetingCommand`` maps to the ``greeing($command)`` method when found on any registered handler.
* ``HelloWorld\GreetedEvent`` is delegated to ``applyGreeted($event)`` when created on the aggregate root
* ``HelloWorld\GreetedEvent`` is passed to all event handlers that have a method ``onGreeted($event)``.
* ``new DomainObjectChanged("Greeted", array("foo" => "bar"))`` is mapped to the "Greeted" event.

## Installation & Requirements

The core library has no dependencies on other libraries. Plugins have dependencies on their specific libraries.

Install with [Composer](http://getcomposer.org):

    {
        "require": {
            "beberlei/lite-cqrs"
        }
    }

## Workflow

CQRS is an asynchroneous/event driven architecture:

1. You push commands into a ``CommandBus``. Commands are simple objects
   extending ``Command`` created by you.
2. The ``CommandBus`` checks for a handler that can execute your command. Every
   command has exactly one handler.
3. The command handler changes state of the domain model. It does that by
   creating events (that represent state change) and passing them to the
   ``AggregateRootInterface::apply()`` method of your domain objects.
4. When the command is completed, the command bus will check all objects in the
   identity map for events.
5. All found events will be passed to the ``EventStoreInterface#add()`` method.
6. The EventStore can save all events to a persistent storage.
7. After storing all events, the event store triggers event handlers that
   listen to the domain events (Pub-Sub).
8. Event Handlers can create new commands again using the ``CommandBus``.

Command and Event handler execution can be wrapped in handlers that manage
transactions. Event store and event handling is outside of any command
transaction. If the command fails with any exception all events created
by the command are forgotten. No event handlers will be triggered in this
case.

In the case of InMemory CommandBus and EventMessageBus LiteCQRS makes sure that
the execution of command and event handlers is never nested, but linearized.
This prevents transactions affecting each other.

## Example

See ``example/example1.php`` for a simple example.

## Setup (Simple InMemory Case, no persistence)

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
use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Bus\MessageInterface;

class CommandLogger implements MessageHandlerInterface
{
    private $next;

    public function __construct(MessageHandlerInterface $next)
    {
        $this->next = $next;
    }

    public function handle(MessageInterface $command)
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

The same is possible for the ``EventMessageBus``.

## Extension Points

You should implement your own ``CommandBus`` or extend the existing to wire the whole process together
exactly as you need it to work.

## Plugins

### Doctrine

Doctrine Plugin ships with transactional wrapper handlers for Commands and Events:

- ``LiteCQRS\Plugin\Doctrine\MessageHandler\DbalTransactionalHandler``
- ``LiteCQRS\Plugin\Doctrine\MessageHandler\OrmTransactionalHandler``

Also to synchronize the events to event storage you can use the IdentityMapListener:

- ``LiteCQRS\Plugin\Doctrine\IdentityMapListener``

### Symfony

Inside symfony you can use LiteCQRS by registering services with ``litecqrs.command_handler``
or the ``litecqrs.event_handler`` tag. These services are then autodiscovered for commands
and events.

Container Aware implementations of ``CommandBus`` and ``EventMessageBus`` implement lazy loading
of all command- and event handlers for better performance.

### Swiftmailer

The Swiftmailer Plugin allows you to defer the sending of mails until after a command or event
handler has actually finished successfully.

- ``LiteCQRS\Plugin\Swiftmailer\SpoolTransportHandler``

You need a spool transport and a real transport instance for this. The Spool transport queues
all messages and the transport handler sends all messages through the real transport, if the
command/event handler was executed successfully.

