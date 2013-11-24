<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\AggregateRootNotFoundException;
use LiteCQRS\AggregateRoot;
use LiteCQRS\DefaultDomainEvent;

use Rhumsaa\Uuid\Uuid;

class EventSourceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_aggregate_root_loaded_from_event_stream()
    {
        $uuid = Uuid::uuid4();
        $eventStream = new EventStream('LiteCQRS\EventStore\EventSourcedAggregate', $uuid, array(new TestEvent()));

        $eventStore = $this->mockEventStoreReturning($uuid, $eventStream);
        $repository = new EventSourceRepository($eventStore);

        $entity = $repository->find('LiteCQRS\EventStore\EventSourcedAggregate', $uuid);

        $this->assertTrue($entity->eventApplied);
        $this->assertSame($uuid, $entity->getId());
    }

    /**
     * @test
     */
    public function it_throws_not_found_exception_when_classnames_missmatch()
    {
        $uuid = Uuid::uuid4();
        $eventStream = new EventStream('LiteCQRS\EventStore\EventSourcedAggregate', $uuid, array(new TestEvent()));

        $eventStore = $this->mockEventStoreReturning($uuid, $eventStream);
        $repository = new EventSourceRepository($eventStore);

        $this->setExpectedException('LiteCQRS\AggregateRootNotFoundException');

        $entity = $repository->find('stdClass', $uuid);
    }

    /**
     * @test
     */
    public function it_throws_not_found_exception_when_no_eventstream_found()
    {
        $uuid = Uuid::uuid4();

        $eventStore = \Phake::mock('LiteCQRS\EventStore\EventStore');
        \Phake::when($eventStore)->find($uuid)->thenThrow(new AggregateRootNotFoundException());

        $repository = new EventSourceRepository($eventStore);

        $this->setExpectedException('LiteCQRS\AggregateRootNotFoundException');

        $repository->find('stdClass', $uuid);
    }

    protected function mockEventStoreReturning($uuid, $eventStream)
    {
        $eventStore = \Phake::mock('LiteCQRS\EventStore\EventStore');

        \Phake::when($eventStore)->find($uuid)->thenReturn($eventStream);

        return $eventStore;
    }

    /**
     * @test
     */
    public function it_commits_eventstream_when_adding_aggregate()
    {
        $object = new EventSourcedAggregate(Uuid::uuid4());

        $eventStore = \Phake::mock('LiteCQRS\EventStore\EventStore');
        $repository = new EventSourceRepository($eventStore);

        $repository->add($object);

        \Phake::verify($eventStore)->commit($object->getEventStream());

        $this->assertEquals('LiteCQRS\EventStore\EventSourcedAggregate', $object->getEventStream()->getClassName());
    }

    /**
     * @test
     */
    public function it_deletes_eventstream_when_removing_aggregate()
    {
        $object = new EventSourcedAggregate(Uuid::uuid4());

        $eventStore = \Phake::mock('LiteCQRS\EventStore\EventStore');
        $repository = new EventSourceRepository($eventStore);

        $repository->remove($object);

        \Phake::verify($eventStore)->delete($object->getEventStream());
    } 
}

class EventSourcedAggregate extends AggregateRoot
{
    public $eventApplied = false;

    public function __construct(Uuid $uuid)
    {
        $this->setId($uuid);
    }

    protected function applyTest(TestEvent $event)
    {
        $this->eventApplied = true;
    }
}

class TestEvent extends DefaultDomainEvent
{
}
