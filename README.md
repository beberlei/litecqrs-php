# LiteCQRS for PHP

Small naming-convention based CQRS library for PHP (loosely based on [LiteCQRS for
C#](https://github.com/danielwertheim/LiteCQRS)) that relies on the Message Bus,
Command, Event and Domain Event patterns.

## Terminology

CQS is Command-Query-Separation: A paradigm where read methods never change
state and write methods never return data.  Build on top, CQRS suggests the
separation of read- from write-model and uses the [DomainEvent
pattern](http://martinfowler.com/eaaDev/DomainEvent.html) to notify the read
model about changes in the write model.

LiteCQRS uses the command pattern and a central message bus service that
finds the corresponding handler to execute a command. A command is just a class
with some properties describing it, it can optionally implement ``LiteCQRS\Command``.

During the execution of a command, domain events can be triggered. These are
again just simple classes with some properties and they can optionally implement
``LiteCQRS\DomainEvent``.

An event queue knows what domain events have been triggered during a command
and then publishes them to an event message bus, where many listeners can
listen to them.

## Changes

### From 1.0 to 1.1

* Extending ``LiteCQRS\Command`` and ``LiteCQRS\DomainEvent`` is NOT required anymore.
  In fact you can use any class as command or event. The naming conventions alone
  make sure command handlers and event listeners are detected.

* JMS Serializer Plugin cannot "detach" aggregate root properties that are part
  of an event that is serialized anymore. Putting related aggregate roots into
  an Event is therefore not supported anymore (and not a good idea even with
  JMS Serializer 0.9 anyways).

## Conventions

* All public methods of a command handler class are mapped to Commands "Command
  Class Shortname" => "MethodName" when the method and command class shortname match.
  Implementing an interface for the commands is NOT required (since 1.1)
* Domain Events are applied to Event Handlers "Event Class Shortname" =>
  "onEventClassShortname". Only if this matches is an event listener registered.
* Domain Events are applied on Entities/Aggregate Roots "Event Class Shortname"
  => "applyEventClassShortname"
* You can optionally extend the ``DefaultDomainEvent`` which has a constructor
  that maps its array input to properties and throws an exception if an unknown
  property is passed.
* There is also a ``DefaultCommand`` with the same semantics as
  ``DefaultDomainEvent``. Extending this is not required.

Examples:

* ``HelloWorld\GreetingCommand`` maps to the ``greeting(GreetingCommand $command)`` method on the registered handler.
* ``HelloWorld\Commands\Greeting`` maps to the ``greeting(Greeting $command)`` method on the registered handler.
* ``HelloWorld\GreetedEvent`` is passed to all event handlers that have a method ``onGreeted(GreetedEvent $event)``.
* ``HelloWorld\Events\Greeted`` is passed to all event handlers that have a method ``onGreeted(Greeted $event)``.
* ``HelloWorld\GreetedEvent`` is delegated to ``applyGreeted($event)`` when created on the aggregate root

## Installation & Requirements

The core library has no dependencies on other libraries. Plugins have dependencies on their specific libraries.

Install with [Composer](http://getcomposer.org):

    {
        "require": {
            "beberlei/lite-cqrs": "dev-master"
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
   ``AggregateRoot::apply()`` or ``DomainEventProvider::raise()`` method of your domain objects.
4. When the command is completed, the command bus will check all objects in the
   identity map for events.
5. All found events will be passed to the ``EventMessageBus#publish()`` method.
6. The EventMessageBus dispatches all events to observing event handlers.
8. Event Handlers can create new commands again using the ``CommandBus``.

Command and Event handler execution can be wrapped in handlers that manage
transactions. Event handling is always triggered outside of any command
transaction. If the command fails with any exception all events created by the
command are forgotten/ignored. No event handlers will be triggered in this case.

In the case of InMemory CommandBus and EventMessageBus LiteCQRS makes sure that
the execution of command and event handlers is never nested, but in sequential
linearized order. This prevents independent transactions for each command
from affecting each other.

## Examples

See [examples/](https://github.com/beberlei/litecqrs-php/tree/master/example) for
some examples:

1. ``example1.php`` shows usage of the Command- and EventMessageBus with one domain object
2. ``example2_event.php`` shows direct usage of the EventMessageBus inside a command
3. ``example3_sequential_commands.php`` demonstrates how commands are processed sequentially.
4. ``tictactoe.php`` implements a tic tac toe game with CQRS.
5. ``SymfonyExample.md`` shows ``example1.php`` implemented within the scope of a Symfony2 project.

## Setup

1. In Memory Command Handlers, no event publishing/observing

```php
<?php
$userService = new UserService();

$commandBus = new DirectCommandBus()
$commandBus->register('MyApp\ChangeEmailCommand', $userService);
```

2. In Memory Commands and Events Handlers

This uses ``LiteCQRS\EventProviderInterface`` instances to trigger domain events.

```php
<?php
// 1. Setup the Library with InMemory Handlers
$messageBus = new InMemoryEventMessageBus();
$identityMap = new SimpleIdentityMap();
$queue = new EventProviderQueue($identityMap);
$commandBus = new DirectCommandBus(array(
    new EventMessageHandlerFactory($messageBus, $queue)
));

// 2. Register a command service and an event handler
$userService = new UserService($identityMap);
$commandBus->register('MyApp\ChangeEmailCommand', $userService);

$someEventHandler = new MyEventHandler();
$messageBus->register($someEventHandler);
```

3. In Memory Commands + Custom Event Queue

LiteCQRS knows about triggered events by asking ``LiteCQRS\Bus\EventQueue``.
Provide your own implementation to be independent of
your domain objects having to implement ``EventProviderInterface``.

```php
<?php
$messageBus = new InMemoryEventMessageBus();
$queue = new MyCustomEventQueue();

$commandBus = new DirectCommandBus(array(
    new EventMessageHandlerFactory($messageBus, $queue)
));
```

## Usage

### To implement a Use Case of your application

1. Create a command object that receives all the necessary input values. Use public properties and extend ``LiteCQRS\DefaultCommand`` to simplify.
2. Add a new method with the name of the command to any of your services (command handler)
3. Register the command handler to handle the given command on the CommandBus.
4. Have your entities implement ``LiteCQRS\AggregateRoot`` or ``LiteCQRS\DomainEventProvider``
5. Use protected method ``raise(DomainEvent $event)`` or apply(DomainEvent $event)`` to attach
   events to your aggregate root objects.

That is all there is for simple use-cases.

If your command triggers events that listeners check for, you should:

1. Create a domain specific event class. Use public properties to simplify.
2. Create a event handler(s) or add method(s) to existing event handler(s).

While it seems "complicated" to create commands and events for every use-case. These objects are really
dumb and only contain public properties. Using your IDE or editor functionality you can easily generate
them in no time. In turn, they will make your code very explicit.

### Difference between apply() and raise()

There are two ways to publish events to the outside world.

- ``DomainEventProvider#raise(DomainEvent $event)`` is the simple one, it emits an event and does nothing more.
- ``AggregateRoot#apply(DomainEvent $event)`` requires you to add a method ``apply$eventName($event)`` that can be used to replay events on objects. This is used to replay an object from events.

If you don't use event sourcing then you are fine just using ``raise()`` and ignoring ``apply()`` altogether.

### Automatic Event Publishing from IdentityMap

You have to implement a mechanism to fill the ```IdentityMapInterface```.
All aggregate root objects in this Identity Map will have their
Events stored and published through EventMessageBus. All other events
will be forgotten!

Example: The Doctrine ORM Plugin has an implementation of the `IdentityMapInterface``.

### Command/Event Handler Proxies

If you want to wrap the command/event handling inside custom logic, you have to extend the ``MessageHandlerInterface``
and pass a proxy factory closure/invokable object into the ```MessageHandlerInterface```.

If you want to log all commands:

```php
<?php
use LiteCQRS\Bus\MessageHandlerInterface;

class CommandLogger implements MessageHandlerInterface
{
    private $next;

    public function __construct(MessageHandlerInterface $next)
    {
        $this->next = $next;
    }

    public function handle($command)
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

### Failing Events

The EventMessageBus prevents exceptions from bubbling up. To allow some debugging of failed event handler
execution there is a special event "EventExecutionFailed" that you can listen to. You will get passed
an instance of ``LiteCQRS\Bus\EventExecutionFailed`` with properties ``$exception``, ``$service`` and
``$event`` to allow analysing failures in your application.

## Extension Points

You should implement your own ``CommandBus`` or extend the existing to wire the whole process together
exactly as you need it to work.

## Plugins

### Doctrine

Doctrine Plugin ships with transactional wrapper handlers for Commands and Events:

- ``LiteCQRS\Plugin\Doctrine\MessageHandler\DbalTransactionalHandler``
- ``LiteCQRS\Plugin\Doctrine\MessageHandler\OrmTransactionalHandler``

Also to synchronize the events to event message bus you can use the DoctrineIdentityMap:

- ``LiteCQRS\Plugin\Doctrine\DoctrineIdentityMap``

It also ships with an implementation of ``DomainEventProviderRepositoryInterface`` wrapping
the EntityManager:

- ``LiteCQRS\Plugin\Doctrine\ORMRepository``

### Silex

Silex plugin ships with a CommandBus and a EventMessageBus that knows how to get services out of
your Silex application as well as a ServiceProvider. The ServiceProvider adds the most basic services
to get LiteCQRS to run.

To enable the service provider register it on your application:

``` php
<?php
$app->register(new LiteCQRS\Plugin\Silex\Provider\LiteCQRSServiceProvider());
```

`lite_cqrs.commands` is automatically injected into the `ApplicationCommandBus`. So to add Commands to
the bus extend the service with:

``` php
<?php

$app['lite_cqrs.commands'] = array_merge($app['lite_cqrs.commands'], array(
    'MyCustom\\SearchCommand' => 'search_handler',
));
```

Remember that the key have to be the Command class and the value must be the service id that have the right
handler method implemented.

To add a EventHandler for a specific event it is needed to call `registerServices` on the `lite_cqrs.event_bus`
service.

The array given to `registerServices` must look like:

``` php
<?php

$eventServices = array(
    'EventName' => 'service_id_id', // or
    'AnotherEvent => array(
        'service_id_1',
        'service_id_2',
    ),
);
```


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

To enable the bundle put the following in your Kernel:

```php
new \LiteCQRS\Plugin\SymfonyBundle\LiteCQRSBundle(),
```

You can enable/disable the different plugins by adding the following to your config.yml:

    lite_cqrs:
        orm:                    true
        swift_mailer:           true
        monolog:                true
        jms_serializer:         true
        crud:                   true
        dbal_event_store:       true
        couchdb_event_store:    true
        couchdb_odm:            true

Please refer to the [SymfonyExample.md](https://github.com/beberlei/litecqrs-php/blob/master/example/SymfonyExample.md)
document for a full demonstration of using LiteCQRS from within a Symfony2 project.

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

The Monolog integration into Symfony registers a specific channel ``lite_cqrs``
which you can configure differently from the default channels in Symfony.  See
[the Symfony
cookbook](http://symfony.com/doc/master/cookbook/logging/channels_handlers.html)
for more information.

### JMS Serializer

WARNING: This plugin does not work with JMS Serializer 0.10 and up, disable
it with ``serializer: false`` in Symfony.

A plugin that uses JMS Serializer to serialize events to JSON. This is necessary
for advanced logging of your events. It uses a custom type handler to convert
aggregate root objects in the events into references and fetches them again
on reconstruction. This way you don't serialize graphs of data into the event store.

### Doctrine CouchDB

A plugin that contains a CouchDB EventStore and Transactional Handler for
Doctrine CouchDB ODM.

### CRUD

While normally CRUD and CQRS don't match, if you use Doctrine as a primary data-source in the
write model then with PHPs dynamic capabilities, you can decently do CRUD with LiteCQRS and
this plugin.

Using ``AggregateResource`` abstract class or the ``CrudCreatable``, ``CrudUpdatable`` and
``CrudDeletable`` traits you can implement CRUD functionality. This is possible to three commands:

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

