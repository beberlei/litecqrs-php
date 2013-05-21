Symfony Example
===============

This document shows an example of how to implement [example1.php](https://github.com/beberlei/litecqrs-php/blob/master/example/example1.php) as part of a Symfony project.

Please note that there may be another way to do this, but as there currently wasn't any documentation, this was how I (@mbadolato) got the example properly working for me.

Service Definition
------------------

```xml
<!-- src/Acme/DemoBundle/Resources/config/services.xml -->

<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <parameters>
        <parameter key="test.event_handler.class">Acme\DemoBundle\EventHandlers\MyEventHandler</parameter>
        <parameter key="test.user_service.class">Acme\DemoBundle\Services\UserService</parameter>
    </parameters>

    <services>
        <service id="test.command.event_handler" class="%test.event_handler.class%">
            <tag name="lite_cqrs.event_handler" />
        </service>

        <service id="test.command.user_service_commands" class="%test.user_service.class%">
            <argument type="service" id="litecqrs.identity_map" />
            <tag name="lite_cqrs.command_handler" />
        </service>
    </services>
</container>
```

User.php
--------

```php
<?php

// src/Acme/DemoBundle/Entity/User.php

namespace Acme\DemoBundle\Entity;

use LiteCQRS\DomainEventProvider;
use LiteCQRS\DomainObjectChanged;

class User extends DomainEventProvider
{
    private $email = "old@example.com";

    public function changeEmail($email)
    {
        $this->raise(new DomainObjectChanged("ChangeEmail", array("email" => $email, "oldEmail" => $this->email)));

        $this->email = $email;
    }
}
```

ChangeEmailCommand.php
----------------------

```php
<?php

// src/Acme/DemoBundle/Model/Command/ChangeEmailCommand.php

namespace Acme\DemoBundle\Model\Command;

use LiteCQRS\DefaultCommand;

class ChangeEmailCommand extends DefaultCommand
{
    public $id;
    public $email;
}
```

UserService.php
---------------

```php

<?php

// src/AcmeDemoBundle/Services/UserService.php

namespace Acme\DemoBundle\Services;

use Acme\DemoBundle\Entity\User;
use Acme\DemoBundle\Model\Command\ChangeEmailCommand;
use LiteCQRS\Bus\IdentityMap\SimpleIdentityMap;

class UserService
{
    private $map;
    private $users;

    public function __construct(SimpleIdentityMap $map)
    {
        $this->map = $map;
    }

    public function changeEmail(ChangeEmailCommand $command)
    {
        $user = $this->findUserById($command->id);
        $user->changeEmail($command->email);
    }

    private function findUserById($id)
    {
        if (! isset($this->users[$id])) {
            // here would normally be a database call or something
            $this->users[$id] = new User();
            $this->map->add($this->users[$id]);
        }

        return $this->users[$id];
    }
}
```

MyEventHandler.php
------------------

```php
<?php

// src/Acme/DemoBundle/EventHandlers/MyEventHandler.php

namespace Acme\DemoBundle\EventHandlers;

use LiteCQRS\DomainObjectChanged;

class MyEventHandler
{
    public function onChangeEmail(DomainObjectChanged $event)
    {
        echo "E-Mail changed from " . $event->oldEmail . " to " . $event->email . "\n";
    }
}
```

Usage Example, via a Symfony Command
------------------------------------

```php
<?php

// src/Acme/DemoBundle/Command/TestCommand

namespace Acme\DemoBundle\Command;

use Acme\DemoBundle\Model\Command\ChangeEmailCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('acme:demo:change-email')
            ->setDescription('Change Email')
            ->addArgument('new_email', InputArgument::REQUIRED, 'Change the email to what?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandBus = $this->getContainer()->get('command_bus');
        $commandBus->handle(new ChangeEmailCommand(array('id' => 1234, 'email' => $input->getArgument('new_email'))));
    }
}
```
Defined commands and events can be displayed:

```bash
$ php app/console lite-cqrs:debug
COMMANDS
========

Command-Handler Service            Command                 Class
test.command.user_service_commands ChangeEmailCommand      Acme\DemoBundle\Model\Command\ChangeEmailCommand


EVENTS
======

Event-Handler Service      Event       Class
test.command.event_handler ChangeEmail Acme\DemoBundle\EventHandlers\MyEventHandler
```

Run the command:

```bash
$ php app/console acme:demo:change-email info@beberlei.de
E-Mail changed from old@example.com to info@beberlei.de
```