<?php

namespace EventSourced;

use Rhumsaa\Uuid\Uuid;
use LiteCQRS\DefaultDomainEvent;
use LiteCQRS\AggregateRoot;

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

    public function __construct($id)
    {
        $this->id = $id;
    }
}

class InventoryItemRenamed extends DefaultDomainEvent
{
    protected $id;
    protected $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}

class InventoryItemDeactivated extends DefaultDomainEvent
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}

$item = new InventoryItem(Uuid::uuid4());
$item->changeName("Foo");
$item->deactivate();

var_dump($item);
