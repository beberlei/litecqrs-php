<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\AggregateRoot;
use LidskaSila\Glow\AggregateRootNotFoundException;
use LidskaSila\Glow\Eventing\EventMessageBus;
use PHPUnit\Framework\TestCase;

class EventSourceRepositoryTest extends TestCase
{

    /** @var \Mockery\MockInterface */
	private $eventBus;

	public function setUp()
	{
		$this->eventBus = \Mockery::mock(EventMessageBus::class);
	}

	/**
	 * @test
	 */
	public function it_returns_aggregate_root_loaded_from_event_stream()
	{
		$id = new EventSourcedAggregateId();

		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid(), [ new TestEvent($id) ]);
		$eventStore  = $this->mockEventStoreReturning($id->getUuid(), $eventStream);
		$repository  = new EventSourceRepository($eventStore, $this->eventBus);

		$entity = $repository->find($id->getUuid(), EventSourcedAggregate::class);

		self::assertTrue($entity->eventApplied);
		self::assertSame($id, $entity->getId());
	}

	protected function mockEventStoreReturning($uuid, $eventStream)
	{
		$eventStore = \Mockery::mock(EventStore::class);

        $eventStore->shouldReceive()->with($uuid)->andReturn($eventStream);

		return $eventStore;
	}

	/**
	 * @test
	 */
	public function it_returns_specific_aggregate_root_too_when_asking_for_parent()
	{
		$id = new EventSourcedAggregateId();

		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid(), [ new TestEvent($id) ]);
		$eventStore  = $this->mockEventStoreReturning($id->getUuid(), $eventStream);
		$repository  = new EventSourceRepository($eventStore, $this->eventBus);

		$entity = $repository->find($id->getUuid(), AggregateRoot::class);

		self::assertTrue($entity->eventApplied);
		self::assertSame($id, $entity->getId());
	}

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_classnames_missmatch()
	{
		$id = new EventSourcedAggregateId();

		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid(), [ new TestEvent($id) ]);
		$eventStore  = $this->mockEventStoreReturning($id->getUuid(), $eventStream);
		$repository  = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException(AggregateRootNotFoundException::class);

		$entity = $repository->find($id->getUuid(), 'stdClass');
	}

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_no_eventstream_found()
	{
		$id = new EventSourcedAggregateId();

		$eventStore = \Mockery::mock(EventStore::class)->shouldReceive($id->getUuid())->andThrow(new AggregateRootNotFoundException());
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException(AggregateRootNotFoundException::class);

		$repository->find($id->getUuid(), 'stdClass');
	}

	/**
	 * @test
	 */
	public function it_commits_eventstream_when_adding_aggregate()
	{
		$id     = new EventSourcedAggregateId();
		$object = new EventSourcedAggregate($id);
		$event  = new TestEvent($id);
		$event->setAggregateId($id);
		$tx     = new Transaction(new EventStream('foo', $object->getId()->getUuid()), [ $event ]);

		$eventStore = self::getMockBuilder(EventStore::class)->setMethods([ 'commit', 'find' ])->getMock();
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$eventStore->expects(self::once())->method('commit')->with(self::isInstanceOf(EventStream::class))->willReturn($tx);
		$repository->save($object);

		$this->eventBus->shouldReceive('publish')->with($event);

		self::assertEquals($id->getUuid(), $event->getAggregateId()->getUuid());
	}

	/**
	 * @test
	 */
	public function it_throws_concurrency_exception_when_versions_missmatch()
	{
		$id          = new EventSourcedAggregateId();
		$eventStream = new EventStream(EventSourcedAggregate::class, $id->getUuid());

		$eventStore = $this->mockEventStoreReturning($id->getUuid(), $eventStream);

		self::expectException(ConcurrencyException::class);

		$repository = new EventSourceRepository($eventStore, $this->eventBus);
		$repository->find($id->getUuid(), EventSourcedAggregate::class, 1337);
	}
}
