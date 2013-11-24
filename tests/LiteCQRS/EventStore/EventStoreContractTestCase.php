<?php

namespace LiteCQRS\EventStore;

use Rhumsaa\Uuid\Uuid;

abstract class EventStoreContractTestCase extends \PHPUnit_Framework_TestCase
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

    protected function givenFixtureStreamWith($uuid)
    {
        $testEvent = new EventStoreTestEvent();
        $fixtureStream = new EventStream('LiteCQRS\EventStore\EventSourcedAggregate', $uuid, array($testEvent), 1337);

        return $fixtureStream;
    }

    /**
     * @test
     */
    public function it_stores_events_when_commiting_event_stream()
    {
        $uuid = Uuid::uuid4();
        $fixtureStream = $this->givenFixtureStreamWith($uuid);
        $fixtureStream->addEvent(new EventStoreTestEvent());

        $eventStore = $this->givenAnEventStore();
        $transaction = $this->whenCommittingEventStream($eventStore, $fixtureStream);

        $this->thenReturnedTransactionContains($transaction, $fixtureStream);
        $this->thenStorageContains($fixtureStream);
    }

    protected function thenReturnedTransactionContains(Transaction $transaction, EventStream $eventStream)
    {
        $this->assertInstanceOf('LiteCQRS\EventStore\Transaction', $transaction);
        $this->assertSame($eventStream, $transaction->getEventStream());
    }

    abstract protected function thenStorageContains(EventStream $stream);

    protected function whenCommittingEventStream(EventStore $eventStore, EventStream $fixtureStream)
    {
        return $eventStore->commit($fixtureStream);
    }

    protected function thenStoredEventStreamEqualsFixtureStream($eventStream, $fixtureStream)
    {
        $this->assertInstanceOf('LiteCQRS\EventStore\EventStream', $eventStream);
        $this->assertEquals($fixtureStream, $eventStream);
    }

    abstract protected function givenAnEventStore();

    abstract protected function givenEventStoreContains(EventStore $eventStore, EventStream $eventStream);

    protected function expectEventStreamNotFoundException()
    {
        $this->setExpectedException('LiteCQRS\EventStore\EventStreamNotFoundException');
    }

    protected function whenFindingStreamWith(EventStore $eventStore, $uuid)
    {
        return $eventStore->find($uuid);
    }
}

class EventStoreTestEvent extends \LiteCQRS\DefaultDomainEvent
{
}
