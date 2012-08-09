# LiteCQRS for PHP

Small convention based CQRS library for PHP (loosly based on [LiteCQRS for C#](https://github.com/danielwertheim/LiteCQRS)).

Conventions are:

* All public methods of a command handler class are mapped to Commands "Command Class Shortname" => "MethodName", for example "MyLib\DoSomethingCommand" => "doSomething($command)"
* Domain Events are applied on Entities/Aggregate Roots "Event Class Shortname" => "applyEventClassShortname", for example "MyLib\SomethingDoneEvent => "applySomethingDone($event)"
* Domain Events are applied to Event Handlers "Event Class Shortname" => "onEventClassShortname", for example "MyLib\SomethingDoneEvent" => "onSomethingDone($event)"

As long as your events don't have listeners, you can also use the ``DomainObjectChanged`` Event. You can dynamically set the event name on it and exchange it with a real event implementation when it becomes necessary.

You can stick yourself together a simple CQRS application by implementing a ``CommandBus` and an ``EventMessageBus``.

```php
<?php

namespace MyApp;

use LiteCQRS\BaseAggregateRoot;
use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Command;
use LiteCQRS\DomainObjectChanged;

$commandBus = new DirectCommandBus();
$commandBus->register('MyApp\ChangeEmailCommand', $userService);

// Invoke!
$commandBus->handle(new ChangeEmailCommand(1234, 'kontakt@beberlei.de'));

class UserService
{
    public function changeEmail(ChangeEmailCommand $command)
    {
        $user = $this->repository->find($command->id);
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

You should implement your own ``CommandBus`` to wire the whole process together. The following pieces
are deliberatly unimplemented in this basic library CommandBus:

1. Wrap commands into a transaction of your storage engines.
2. Pass all "appliedEvents" to the EventMessageBus triggering the events.
3. Implement an EventStorage to actually save the events.
4. Load services lazily through a Container
