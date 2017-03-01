<?php

namespace LiteCQRS\EventStore;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

abstract class EventStoreContractTestCase extends TestCase
{

	/**
	 * @test
	 */
	public function it_throws_not_found_exception_when_no_stream_exists()
	{
		$uuid = Uuid::uuid4();

		$eventStore = $this->givenAnEventStore();
		$this->expectEventStreamNotFoundException();
		$this->whenFindingStreamWith($eventStore, $uuid);
	}

	/**
	 * @test
	 */
	public function it_constructs_event_stream_when_data_exists()
	{
		$uuid = Uuid::uuid4();

		$fixtureStream = $this->givenFixtureStreamWith($uuid);

		$eventStore = $this->givenAnEventStore();
		$this->givenEventStoreContains($eventStore, $fixtureStream);
		$eventStream = $this->whenFindingStreamWith($eventStore, $uuid);

		$this->thenStoredEventStreamEqualsFixtureStream($eventStream, $fixtureStream);
	}

	/**
	 * @test
	 */
	public function it_stores_events_when_commiting_event_stream()
	{
		$uuid          = Uuid::uuid4();
		$fixtureStream = $this->givenFixtureStreamWith($uuid);
		$fixtureStream->addEvent(new EventStoreTestEvent());

		$eventStore  = $this->givenAnEventStore();
		$transaction = $this->whenCommittingEventStream($eventStore, $fixtureStream);

		$this->thenReturnedTransactionContains($transaction, $fixtureStream);
		$this->thenStorageContains($fixtureStream);
	}

	/**
	 * @test
	 */
	public function it_ignores_empty_event_streams()
	{
		$uuid          = Uuid::uuid4();
		$fixtureStream = $this->givenFixtureStreamWith($uuid);

		$eventStore  = $this->givenAnEventStore();
		$transaction = $this->whenCommittingEventStream($eventStore, $fixtureStream);

		$this->expectEventStreamNotFoundException();
		$this->whenFindingStreamWith($eventStore, $uuid);
	}

	/**
	 * @test
	 */
	public function it_throws_concurrency_exception_when_committing_wrong_current_version()
	{
		$uuid = Uuid::uuid4();

		$fixtureStream = $this->givenFixtureStreamWith($uuid);
		$fixtureStream->markNewEventsProcessed(10);

		$commitStream = $this->givenFixtureStreamWith($uuid);
		$commitStream->markNewEventsProcessed(20);
		$commitStream->addEvent(new EventStoreTestEvent());

		$eventStore = $this->givenAnEventStore();
		$this->givenEventStoreContains($eventStore, $fixtureStream);

		$this->expectConcurrencyException();
		$this->whenCommittingEventStream($eventStore, $commitStream);
	}

	/**
	 * @test
	 */
	public function it_manages_versions_such_that_multiple_commits_succeed()
	{
		$uuid = Uuid::uuid4();

		$fixtureStream = $this->givenFixtureStreamWith($uuid);
		$fixtureStream->addEvent(new EventStoreTestEvent());

		$eventStore = $this->givenAnEventStore();

		$this->whenCommittingEventStream($eventStore, $fixtureStream);

		$nextEvent = new EventStoreTestEvent();
		$fixtureStream->addEvent($nextEvent);

		$transaction = $this->whenCommittingEventStream($eventStore, $fixtureStream);
		$this->thenTransactionOnlyContainsNewEvents($transaction, [ $nextEvent ]);
	}

	protected function thenTransactionOnlyContainsNewEvents(Transaction $transaction, array $newEvents)
	{
		self::assertEquals($newEvents, $transaction->getCommittedEvents());
	}

	protected function expectConcurrencyException()
	{
		self::expectException(ConcurrencyException::class);
	}

	protected function thenReturnedTransactionContains(Transaction $transaction, EventStream $eventStream)
	{
		self::assertInstanceOf(Transaction::class, $transaction);
		self::assertSame($eventStream, $transaction->getEventStream());
	}

	abstract protected function thenStorageContains(EventStream $stream);

	abstract protected function givenAnEventStore();

	abstract protected function givenEventStoreContains(EventStore $eventStore, EventStream $eventStream);

	protected function whenCommittingEventStream(EventStore $eventStore, EventStream $fixtureStream)
	{
		return $eventStore->commit($fixtureStream);
	}

	protected function thenStoredEventStreamEqualsFixtureStream($eventStream, $fixtureStream)
	{
		self::assertInstanceOf(EventStream::class, $eventStream);
		self::assertEquals($fixtureStream, $eventStream);
	}

	protected function expectEventStreamNotFoundException()
	{
		self::expectException(EventStreamNotFoundException::class);
	}

	protected function whenFindingStreamWith(EventStore $eventStore, $uuid)
	{
		return $eventStore->find($uuid);
	}

	protected function givenFixtureStreamWith($uuid)
	{
		$testEvent     = new EventStoreTestEvent();
		$fixtureStream = new EventStream(EventSourcedAggregate::class, $uuid, [ $testEvent ], 1337);

		return $fixtureStream;
	}
}
