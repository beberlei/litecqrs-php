<?php
/**
 * Example 1 showed a "domain driven" way of emitting events,
 * however its perfectly possible to dispatch and simple
 * to emit events directly from commands.
 *
 * This shows this functionality with the same example as before,
 * using a "traditional" getter/Setter entity.
 */
namespace MyApp;

require_once __DIR__ . "/../vendor/autoload.php";

use LiteCQRS\AggregateRoot;
use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Bus\InMemoryEventMessageBus;
use LiteCQRS\Bus\EventMessageBus;
use LiteCQRS\Bus\IdentityMap\SimpleIdentityMap;
use LiteCQRS\Bus\IdentityMap\EventProviderQueue;
use LiteCQRS\Bus\EventMessageHandlerFactory;
use LiteCQRS\DefaultCommand;
use LiteCQRS\DomainObjectChanged;

class User extends AggregateRoot
{
    private $email = "old@beberlei.de";

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
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

    public function __construct(SimpleIdentityMap $map, EventMessageBus $eventBus)
    {
        $this->map      = $map;
        $this->eventBus = $eventBus;
    }

    public function changeEmail(ChangeEmailCommand $command)
    {
        $user = $this->findUserById($command->id);

        $oldEmail = $user->getEmail();
        $user->setEmail($command->email);

        $this->eventBus->publish(new DomainObjectChanged(
            "ChangeEmail", array("email" => $command->email, "oldEmail" => $oldEmail))
        );
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
$userService      = new UserService($identityMap, $messageBus);
$someEventHandler = new MyEventHandler();

$commandBus->register('MyApp\ChangeEmailCommand', $userService);
$messageBus->register($someEventHandler);

// 3. Invoke command!
$commandBus->handle(new ChangeEmailCommand(array('id' => 1234, 'email' => 'kontakt@beberlei.de')));
$commandBus->handle(new ChangeEmailCommand(array('id' => 1234, 'email' => 'info@beberlei.de')));

