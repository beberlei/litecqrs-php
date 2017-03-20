<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\AggregateRoot;
use LidskaSila\Glow\AggregateRootNotFoundException;
use LidskaSila\Glow\Eventing\EventMessageBus;
use PHPUnit\Framework\TestCase;

class EventSourceRepositoryTest extends TestCase
{

	private $eventBus;

	public function setUp()
	{
		$this->eventBus = \Phake::mock(EventMessageBus::class);
	}

	/**
	 * @test
	 */
	public function it_returns_aggregate_root_loaded_from_event_stream()
	{
		$id        = new EventSourcedAggregateId();

		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid(), [ new TestEvent($id) ]);
		$eventStore = $this->mockEventStoreReturning($id->getUuid(), $eventStream);
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$entity = $repository->find(EventSourcedAggregate::class, $id->getUuid());

		self::assertTrue($entity->eventApplied);
		self::assertSame($id, $entity->getId());
	}

	protected function mockEventStoreReturning($uuid, $eventStream)
	{
		$eventStore = \Phake::mock(EventStore::class);

		\Phake::when($eventStore)->find($uuid)->thenReturn($eventStream);

		return $eventStore;
	}

	/**
	 * @test
	 */
	public function it_returns_specific_aggregate_root_too_when_asking_for_parent()
	{
		$id        = new EventSourcedAggregateId();

		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid(), [ new TestEvent($id) ]);
		$eventStore = $this->mockEventStoreReturning($id->getUuid(), $eventStream);
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$entity = $repository->find(AggregateRoot::class, $id->getUuid());

		self::assertTrue($entity->eventApplied);
		self::assertSame($id, $entity->getId());
	}

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_classnames_missmatch()
	{
		$id        = new EventSourcedAggregateId();

		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid(), [ new TestEvent($id) ]);
		$eventStore = $this->mockEventStoreReturning($id->getUuid(), $eventStream);
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException(AggregateRootNotFoundException::class);

		$entity = $repository->find('stdClass', $id->getUuid());
	}

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_no_eventstream_found()
	{
		$id        = new EventSourcedAggregateId();

		$eventStore = \Phake::mock(EventStore::class);
		\Phake::when($eventStore)->find($id->getUuid())->thenThrow(new AggregateRootNotFoundException());
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException(AggregateRootNotFoundException::class);

		$repository->find('stdClass', $id->getUuid());
	}

	/**
	 * @test
	 */
	public function it_commits_eventstream_when_adding_aggregate()
	{
		$id        = new EventSourcedAggregateId();
		$object = new EventSourcedAggregate($id);
		$event  = new TestEvent($id);
		$tx     = new Transaction(new EventStream('foo', $object->getId()->getUuid()), [ $event ]);

		$eventStore = self::getMockBuilder(EventStore::class)->setMethods([ 'commit', 'find' ])->getMock();
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$eventStore->expects(self::once())->method('commit')->with(self::isInstanceOf(EventStream::class))->willReturn($tx);
		$repository->save($object);

		\Phake::verify($this->eventBus)->publish($event);

		self::assertEquals($id->getUuid(), $event->getAggregateId()->getUuid());
	}

	/**
	 * @test
	 */
	public function it_throws_concurrency_exception_when_versions_missmatch()
	{
		$id        = new EventSourcedAggregateId();
		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid());

		$eventStore = $this->mockEventStoreReturning($id->getUuid(), $eventStream);

		self::expectException(ConcurrencyException::class);

		$repository = new EventSourceRepository($eventStore, $this->eventBus);
		$repository->find(EventSourcedAggregate::class, $id->getUuid(), 1337);
	}
}
