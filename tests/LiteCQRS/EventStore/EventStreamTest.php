<?php

namespace LiteCQRS\EventStore;

use Rhumsaa\Uuid\Uuid;

class EventStreamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_requires_uuid()
    {
        $uuid = Uuid::uuid4();
        $stream = new EventStream($uuid);

        $this->assertSame($uuid, $stream->getUuid());
    }

    /**
     * @test
     */
    public function it_allows_adding_events()
    {
        $event = \Phake::mock('LiteCQRS\DomainEvent');

        $uuid = Uuid::uuid4();
        $stream = new EventStream($uuid);
        $stream->addEvent($event);

        $actualEvents = iterator_to_array($stream);

        $this->assertSame($event, $actualEvents[0]);
    }

    /**
     * @test
     */
    public function it_keeps_new_events_seperate_from_known_events()
    {
        $oldEvent = \Phake::mock('LiteCQRS\DomainEvent');
        $newEvent = \Phake::mock('LiteCQRS\DomainEvent');

        $uuid = Uuid::uuid4();
        $stream = new EventStream($uuid, array($oldEvent));
        $stream->addEvent($newEvent);

        $actualEvents = iterator_to_array($stream);

        $this->assertSame($oldEvent, $actualEvents[0]);
        $this->assertSame($newEvent, $actualEvents[1]);

        $actualNewEvents = $stream->newEvents();

        $this->assertEquals(1, count($actualNewEvents));
    }

    /**
     * @test
     */
    public function it_can_mark_new_events_as_processed()
    {
        $newEvent = \Phake::mock('LiteCQRS\DomainEvent');

        $uuid = Uuid::uuid4();
        $stream = new EventStream($uuid, array());
        $stream->addEvent($newEvent);

        $stream->markNewEventsProcessed();

        $this->assertEquals(0, count($stream->newEvents()));
    }

    /**
     * @test
     */
    public function it_throws_exception_accessing_unknown_metadata()
    {
        $uuid = Uuid::uuid4();
        $stream = new EventStream($uuid, array());

        $this->setExpectedException('LiteCQRS\EventStore\UnknownMetadataException');
        $stream->getMetadata('Foo');
    }
}
