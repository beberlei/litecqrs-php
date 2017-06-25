<?php
/**
 * This example shows the implementation for a "change email"
 * command on a User entity. The command accepts a user id
 * and a new email address. The change is delegated to the
 * user object where the "DomainObjectChanged" event is raised.
 *
 * A listener picks up this event and displays the changed e-mail.
 */

namespace MyApp;

require_once __DIR__ . "/../vendor/autoload.php";

use LidskaSila\Glow\DomainEventProvider;
use LidskaSila\Glow\Bus\DirectCommandBus;
use LidskaSila\Glow\Bus\InMemoryEventMessageBus;
use LidskaSila\Glow\Bus\IdentityMap\SimpleIdentityMap;
use LidskaSila\Glow\Bus\IdentityMap\EventProviderQueue;
use LidskaSila\Glow\Bus\EventMessageHandlerFactory;
use LidskaSila\Glow\DefaultCommand;
use LidskaSila\Glow\DomainObjectChanged;

class User extends DomainEventProvider
{
    private $email = "old@beberlei.de";

    public function changeEmail($email)
    {
        $this->raise(new DomainObjectChanged("ChangeEmail", array("email" => $email, "oldEmail" => $this->email)));

        $this->email = $email;
    }
}

class ChangeEmailCommand extends DefaultCommand
{
    public $id;
    public $email;
}

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
        if (!isset($this->users[$id])) {
            // here would normally be a database call or something
            $this->users[$id] = new User();
            $this->map->add($this->users[$id]);
        }
        return $this->users[$id];
    }
}

class MyEventHandler
{
    public function onChangeEmail(DomainObjectChanged $event)
    {
        echo "E-Mail changed from " . $event->oldEmail . " to " . $event->email . "\n";
    }
}

// 1. Setup the Library with InMemory Handlers
$messageBus  = new InMemoryEventMessageBus();
$identityMap = new SimpleIdentityMap();
$queue = new EventProviderQueue($identityMap);
$commandBus  = new DirectCommandBus(array(
    new EventMessageHandlerFactory($messageBus, $queue)
));

// 2. Register a command service and an event handler
$userService      = new UserService($identityMap);
$someEventHandler = new MyEventHandler();

$commandBus->register('MyApp\ChangeEmailCommand', $userService);
$messageBus->register($someEventHandler);

// 3. Invoke command!
$commandBus->handle(new ChangeEmailCommand(array('id' => 1234, 'email' => 'kontakt@beberlei.de')));
$commandBus->handle(new ChangeEmailCommand(array('id' => 1234, 'email' => 'info@beberlei.de')));

