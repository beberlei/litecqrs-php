<?php

namespace LiteCQRS\Plugin\CRUD;
use LiteCQRS\AggregateRepositoryInterface;
use LiteCQRS\AggregateRootInterface;
use LiteCQRS\DomainEvent;
use LiteCQRS\Plugin\CRUD\Model\Commands\CreateResourceCommand;
use LiteCQRS\Plugin\CRUD\Model\Commands\UpdateResourceCommand;

/**
 * Tests for the AggregateResource class of the CRUD plugin
 *
 * @author Markus Tacker <m@coderbyheart.de>
 * @package LiteCQRS\Plugin\CRUD
 */
class AggregateResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function updateShouldUseSuppliedData()
    {
        $data                = array('some' => 'data');
        $updateCommand       = new UpdateResourceCommand();
        $updateCommand->id   = 'some-id';
        $updateCommand->data = $data;

        $object = new DummyObject();
        $ar     = new DummyRepo($object);
        $cs     = new CRUDCommandService($ar);
        $cs->updateResource($updateCommand);
        $this->assertEquals($object->event->data, $data);
    }

    /**
     * @test
     */
    public function createShouldUseSuppliedData()
    {
        $data                 = array('some' => 'data');
        $createCommand        = new CreateResourceCommand();
        $createCommand->class = 'LiteCQRS\Plugin\CRUD\DummyObject';
        $createCommand->id    = 'some-id';
        $createCommand->data  = $data;

        $ar = new DummyRepo();
        $cs = new CRUDCommandService($ar);
        $cs->createResource($createCommand);
        $this->assertEquals($ar->addObject->event->data, $data);
    }
}

class DummyObject extends AggregateResource
{
    public $event;

    public $id = 'some-id';

    protected function apply(DomainEvent $event)
    {
        $this->event = $event;
    }
}

class DummyRepo implements AggregateRepositoryInterface
{
    public $addObject;

    private $updateObject;

    public function __construct($updateObject = null)
    {
        $this->updateObject = $updateObject;
    }

    public function find($class, $id)
    {
        assert('$id === "some-id";');
        return $this->updateObject;
    }

    public function add(AggregateRootInterface $object)
    {
        $this->addObject = $object;
    }

    public function remove(AggregateRootInterface $object)
    {
    }
}

