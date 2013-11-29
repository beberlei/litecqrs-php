<?php

namespace EventSourced;

use Rhumsaa\Uuid\Uuid;

use LiteCQRS\Repository;
use LiteCQRS\DefaultCommand;
use LiteCQRS\Commanding;
use LiteCQRS\DefaultDomainEvent;
use LiteCQRS\AggregateRoot;
use LiteCQRS\EventStore;
use LiteCQRS\Serializer\ReflectionSerializer;
use LiteCQRS\Eventing;

require_once __DIR__ . "/../vendor/autoload.php";

class InventoryItem extends AggregateRoot
{
    private $name;
    private $activated = true;

    public function __construct($id)
    {
        $this->apply(new InventoryItemCreated($id));
    }

    public function changeName($name)
    {
        if (!$name) {
            throw new \RuntimeException("Setting empty name is not allowed.");
        }

        $this->apply(new InventoryItemRenamed($this->getId(), $name));
    }

    public function deactivate()
    {
        if (!$this->activated) {
            throw new \RuntimeException("Cannot deactivate item again.");
        }

        $this->apply(new InventoryItemDeactivated($this->getId()));
    }

    protected function applyInventoryItemCreated($event)
    {
        $this->setId($event->id);
    }

    protected function applyInventoryItemRenamed($event)
    {
        $this->name = $event->name;
    }

    protected function applyInventoryItemDeactivated($event)
    {
        $this->activated = false;
    }
}

class InventoryItemCreated extends DefaultDomainEvent
{
    protected $id;

    public function __construct(Uuid $id)
    {
        $this->id = $id;
    }
}

class InventoryItemRenamed extends DefaultDomainEvent
{
    protected $id;
    protected $name;

    public function __construct(Uuid $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

class InventoryItemDeactivated extends DefaultDomainEvent
{
    protected $id;

    public function __construct(Uuid $id)
    {
        $this->id = $id;
    }
}

class CreateInventoryItem extends DefaultCommand
{
    public $id;
}

class ChangeInventoryName extends DefaultCommand
{
    public $id;
    public $newName;
}

class DeactivateInventoryItem extends DefaultCommand
{
    public $id;
}

class InventoryHandler
{
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function createInventoryItem(CreateInventoryItem $command)
    {
        $inventoryItem = new InventoryItem($command->id);

        $this->repository->save($inventoryItem);
    }

    public function changeInventoryName(ChangeInventoryName $command)
    {
        $inventoryItem = $this->repository->find('EventSourced\\InventoryItem', $command->id);
        $inventoryItem->changeName($command->newName);

        $this->repository->save($inventoryItem);
    }

    public function deactivateInventoryItem(DeactivateInventoryItem $command)
    {
        $inventoryItem = $this->repository->find('EventSourced\\InventoryItem', $command->id);
        $inventoryItem->deactivate();

        $this->repository->save($inventoryItem);
    }
}

class InventoryConsoleOutputListener
{
    public function onInventoryItemCreated(InventoryItemCreated $event)
    {
        echo "Creating: " . $event->id . "\n";
    }
}

$listener = new InventoryConsoleOutputListener();
$eventHandlerLocator = new Eventing\MemoryEventHandlerLocator();
$eventHandlerLocator->register($listener);

$eventStore = new EventStore\OptimisticLocking\OptimisticLockingEventStore(
    new EventStore\OptimisticLocking\MemoryStorage(),
    new ReflectionSerializer()
);
$eventBus = new Eventing\SynchronousInProcessEventBus($eventHandlerLocator);

$repository = new EventStore\EventSourceRepository($eventStore, $eventBus);

$inventoryHandler = new InventoryHandler($repository);
$commandHandlerLocator = new Commanding\MemoryCommandHandlerLocator();
$commandHandlerLocator->register('EventSourced\CreateInventoryItem', $inventoryHandler);
$commandHandlerLocator->register('EventSourced\ChangeInventoryName', $inventoryHandler);
$commandHandlerLocator->register('EventSourced\DeactivateInventoryItem', $inventoryHandler);
$commandBus = new Commanding\SequentialCommandBus($commandHandlerLocator);

$id = Uuid::uuid4();

$commandBus->handle(new CreateInventoryItem(array('id' => $id)));
$commandBus->handle(new ChangeInventoryName(array('id' => $id, 'newName' => 'test')));
$commandBus->handle(new DeactivateInventoryItem(array('id' => $id)));
