<?php

namespace LidskaSila\Glow\EventStore;

use LidskaSila\Glow\DomainEvent;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class EventStreamTest extends TestCase
{

	/**
	 * @test
	 */
	public function it_requires_uuid()
	{
		$uuid   = Uuid::uuid4();
		$stream = new EventStream('stdClass', $uuid);

		self::assertSame($uuid, $stream->getUuid());
	}

	/**
	 * @test
	 */
	public function it_allows_adding_events()
	{
		$event = \Mockery::mock(DomainEvent::class);

		$uuid   = Uuid::uuid4();
		$stream = new EventStream('stdClass', $uuid);
		$stream->addEvent($event);

		$actualEvents = iterator_to_array($stream);

		self::assertSame($event, $actualEvents[0]);
	}

	/**
	 * @test
	 */
	public function it_keeps_new_events_seperate_from_known_events()
	{
		$oldEvent = \Mockery::mock(DomainEvent::class);
		$newEvent = \Mockery::mock(DomainEvent::class);

		$uuid   = Uuid::uuid4();
		$stream = new EventStream('stdClass', $uuid, [ $oldEvent ]);
		$stream->addEvent($newEvent);

		$actualEvents = iterator_to_array($stream);

		self::assertSame($oldEvent, $actualEvents[0]);
		self::assertSame($newEvent, $actualEvents[1]);

		$actualNewEvents = $stream->newEvents();

		self::assertEquals(1, count($actualNewEvents));
	}

	/**
	 * @test
	 */
	public function it_can_mark_new_events_as_processed()
	{
		$newEvent = \Mockery::mock(DomainEvent::class);

		$uuid   = Uuid::uuid4();
		$stream = new EventStream('stdClass', $uuid, []);
		$stream->addEvent($newEvent);

		$stream->markNewEventsProcessed();

		self::assertEquals(0, count($stream->newEvents()));
	}
}
