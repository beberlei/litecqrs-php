# LiteCQRS for PHP

Small convention based CQRS + Event Sourcing library for PHP (loosly based on
[LiteCQRS for C#](https://github.com/danielwertheim/LiteCQRS)).  If you want to
use CQRS without event sourcing, this might already be too big for you,
although you can take some of the parts (CommandBus) in isolation to implement
a sole CQRS solution.

## Conventions

* All public methods of a command handler class are mapped to Commands "Command
  Class Shortname" => "MethodName"
* Domain Events are applied on Entities/Aggregate Roots "Event Class Shortname"
  => "applyEventClassShortname"
* Domain Events are applied to Event Handlers "Event Class Shortname" =>
  "onEventClassShortname"
* You can use the ``DomainObjectChanged`` Event to avoid creating lots of event
  classes. You can dynamically set the event name on it and exchange it with a
  real event implementation when it becomes necessary.
* Otherwise you can extend the ``DefaultDomainEvent`` which has a constructor
  that maps its array input to properties and throws an exception if an unknown
  property is passed.
* There is also a ``DefaultCommand`` with the same semantics as
  ``DefaultDomainEvent``

Examples:

* ``HelloWorld\GreetingCommand`` maps to the ``greeting($command)`` method when found on any registered handler.
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

These are the steps that a command regularly takes through the LiteCQRS stack during execution:

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

## Setup

1. In Memory Command Handlers, No EventStore and Event Message Handling

```php
<?php
$userService = new UserService();

$commandBus = new DirectCommandBus()
$commandBus->register('MyApp\ChangeEmailCommand', $userService);
```

2. In Memory Commands and Events, with Event Store

```php
<?php
// 1. Setup the Library with InMemory Handlers
$messageBus = new InMemoryEventMessageBus();
$eventStore = new InMemoryEventStore($messageBus);
$identityMap = new SimpleIdentityMap();
$commandBus = new DirectCommandBus(array(
    new EventStoreHandlerFactory($eventStore, $identityMap)
));

// 2. Register a command service and an event handler
$userService = new UserService($identityMap);
$commandBus->register('MyApp\ChangeEmailCommand', $userService);

$someEventHandler = new MyEventHandler();
$messageBus->register($someEventHandler);
```

## Usage

### To implement a Use Case of your application

1. Create a command object that recieves all the necessary input values. Use public properties to and extend ``LiteCQRS\DefaultCommand`` simplify.
2. Add a new method with the name of the command to any of your services (command handler)
3. Register the command handler to handle the given command on the CommandBus.
4. Have your entities implement ``LiteCQRS\AggregateRoot``
5. Use protected method ``raise(DomainEvent $event)`` or apply(DomainEvent $event)`` to attach
   events to your aggregate root objects.

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
$loggerProxyFactory = function($handler) {
    return new CommandLogger($handler);
};
$commandBus = new DirectCommandBus(array($loggerProxyFactory));
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

It also ships with an implementation of ``AggregateRepositoryInterface`` wrapping
the EntityManager:

- ``LiteCQRS\Plugin\Doctrine\ORMRepository``

### Symfony

Inside symfony you can use LiteCQRS by registering services with
``lite_cqrs.command_handler`` or the ``lite_cqrs.event_handler`` tag. These
services are then autodiscovered for commands and events. You can also add
proxy message handler factories for tags. For both commands and events the tags
are ``lite_cqrs.event_proxy_factory`` and ``lite_cqrs.command_proxy_factory``
respectively.

Container Aware implementations of ``CommandBus`` and ``EventMessageBus``
implement lazy loading of all command- and event handlers for better
performance.

### Swiftmailer

The Swiftmailer Plugin allows you to defer the sending of mails until after a command or event
handler has actually finished successfully.

- ``LiteCQRS\Plugin\Swiftmailer\SpoolTransportHandler``

You need a spool transport and a real transport instance for this. The Spool transport queues
all messages and the transport handler sends all messages through the real transport, if the
command/event handler was executed successfully.

### Monolog

A plugin that logs the execution of every command and handler using
[Monolog](https://github.com/Seldaek/monolog).  It includes the type and name
of the message, its parameters as json and if its execution succeeded or failed.

### CRUD

While normally CRUD and CQRS don't match, if you use Doctrine as a primary data-source in the
write model then with PHPs dynamic capabilities, you can decently do CRUD with LiteCQRS and
this plugin.

Using ``AggregateResource`` abstract class or the ``CrudCreatable``, ``CrudUpdatable`` and
``CrudDeletable`` traits you can implememnt CRUD functionality. This is possible to three commands:

- ``LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand``
- ``LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand``
- ``LiteCQRS\Plugin\CRUD\Model\Commands\DeleteResourceCommand``

They have ``$class``, ``$id`` and ``$data`` properties. On the Create and Update commands,
the ```$data`` is applied to the model using mass assignment. You have to make sure
this is a safe operation for your models by implementing the ``apply*()`` methods yourself
instead of relying on the mass assignment.

After processing one of the following three domain events is emitted:

- ``LiteCQRS\Plugin\CRUD\Model\Events\ResourceCreatedEvent``
- ``LiteCQRS\Plugin\CRUD\Model\Events\ResourceUpdatedEvent``
- ``LiteCQRS\Plugin\CRUD\Model\Events\ResourceDeletedEvent``

