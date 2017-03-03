<?php

namespace Lidskasila\Glow\EventStore;

use Lidskasila\Glow\AggregateRootNotFoundException;
use Lidskasila\Glow\Eventing\EventMessageBus;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

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
		$uuid        = Uuid::uuid4();
		$eventStream = new EventStream(EventSourcedAggregate::class, $uuid, [ new TestEvent() ]);

		$eventStore = $this->mockEventStoreReturning($uuid, $eventStream);
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$entity = $repository->find(EventSourcedAggregate::class, $uuid);

		self::assertTrue($entity->eventApplied);
		self::assertSame($uuid, $entity->getId());
	}

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_classnames_missmatch()
	{
		$uuid        = Uuid::uuid4();
		$eventStream = new EventStream(EventSourcedAggregate::class, $uuid, [ new TestEvent() ]);

		$eventStore = $this->mockEventStoreReturning($uuid, $eventStream);
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException(AggregateRootNotFoundException::class);

		$entity = $repository->find('stdClass', $uuid);
	}

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_no_eventstream_found()
	{
		$uuid = Uuid::uuid4();

		$eventStore = \Phake::mock(EventStore::class);
		\Phake::when($eventStore)->find($uuid)->thenThrow(new AggregateRootNotFoundException());

		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		self::expectException(AggregateRootNotFoundException::class);

		$repository->find('stdClass', $uuid);
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
	public function it_commits_eventstream_when_adding_aggregate()
	{
		$id     = Uuid::uuid4();
		$object = new EventSourcedAggregate($id);
		$event  = new TestEvent();
		$tx     = new Transaction(new EventStream('foo', $object->getId()), [ $event ]);

		$eventStore = self::getMockBuilder(EventStore::class)->setMethods([ 'commit', 'find' ])->getMock();
		$repository = new EventSourceRepository($eventStore, $this->eventBus);

		$eventStore->expects(self::once())->method('commit')->with(self::isInstanceOf(EventStream::class))->willReturn($tx);

		$repository->save($object);

		\Phake::verify($this->eventBus)->publish($event);

		self::assertEquals($id, $event->getAggregateId());
	}

	/**
	 * @test
	 */
	public function it_throws_concurrency_exception_when_versions_missmatch()
	{
		$uuid        = Uuid::uuid4();
		$eventStream = new EventStream(EventSourcedAggregate::class, $uuid);

		$eventStore = $this->mockEventStoreReturning($uuid, $eventStream);

		self::expectException(ConcurrencyException::class);

		$repository = new EventSourceRepository($eventStore, $this->eventBus);
		$repository->find(EventSourcedAggregate::class, $uuid, 1337);
	}
}
