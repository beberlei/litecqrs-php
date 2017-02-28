<?php

namespace LiteCQRS\EventStore;

use LiteCQRS\AggregateRootNotFoundException;
use PHPUnit\Framework\TestCase;
use Rhumsaa\Uuid\Uuid;

class EventSourceRepositoryTest extends TestCase
{

	private $eventBus;

	public function setUp()
	{
		$this->eventBus = \Phake::mock('LiteCQRS\Eventing\EventMessageBus');
	}

	/**
	 * @test
	 */
	public function it_returns_aggregate_root_loaded_from_event_stream()
	{
		$uuid        = Uuid::uuid4();
		$eventStream = new EventStream('LiteCQRS\EventStore\EventSourcedAggregate', $uuid, [ new TestEvent() ]);

		$eventStore = $this->mockEventStoreReturning($uuid, $eventStream);
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$entity = $repository->find('LiteCQRS\EventStore\EventSourcedAggregate', $uuid);

		self::assertTrue($entity->eventApplied);
		self::assertSame($uuid, $entity->getId());
	}

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_classnames_missmatch()
	{
		$uuid        = Uuid::uuid4();
		$eventStream = new EventStream('LiteCQRS\EventStore\EventSourcedAggregate', $uuid, [ new TestEvent() ]);

		$eventStore = $this->mockEventStoreReturning($uuid, $eventStream);
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException('LiteCQRS\AggregateRootNotFoundException');

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

		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException('LiteCQRS\AggregateRootNotFoundException');

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
		$id     = Uuid::uuid4();
		$object = new EventSourcedAggregate($id);
		$event  = new TestEvent();
		$tx     = new Transaction(new EventStream('foo', $object->getId()), [ $event ]);

		$eventStore = self::getMockBuilder('LiteCQRS\EventStore\EventStore')->setMethods([ 'commit', 'find' ])->getMock();
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$eventStore->expects(self::once())->method('commit')->with(self::isInstanceOf(EventStream::class))->willReturn($tx);

		$repository->save($object);

		\Phake::verify($this->eventBus)->publish($event);

		$this->assertEquals($id, $event->getAggregateId());
	}

	/**
	 * @test
	 */
	public function it_throws_concurrency_exception_when_versions_missmatch()
	{
		$uuid        = Uuid::uuid4();
		$eventStream = new EventStream('LiteCQRS\EventStore\EventSourcedAggregate', $uuid);

		$eventStore = $this->mockEventStoreReturning($uuid, $eventStream);

		self::expectException('LiteCQRS\EventStore\ConcurrencyException');

		$repository = new EventSourceRepository($eventStore, $this->eventBus);
		$repository->find('LiteCQRS\EventStore\EventSourcedAggregate', $uuid, 1337);
	}
}
