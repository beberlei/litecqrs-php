<?php

namespace LidskaSila\Glow;

use LidskaSila\Glow\EventStore\EventStream;
use LidskaSila\Glow\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;

class AggregateRootTest extends TestCase
{

	/**
	 * @test
	 */
	public function it_manages_id()
	{
		$id     = new SampleAggregateRootId();
		$sample = new SampleAggregateRoot($id);

		self::assertSame($id, $sample->getId());
		self::assertSame($id->getUuid(), $sample->getEventStreamId());
	}

	/**
	 * @test
	 */
	public function it_calls_apply_methods_for_events()
	{
		$id     = new SampleAggregateRootId();
		$sample = new SampleAggregateRoot($id);

		self::assertTrue($sample->loadedFromEvents);
		self::assertEquals('bar', $sample->foo);
	}

	/**
	 * @test
	 */
	public function it_hydrates_from_eventstream()
	{
		$id = new SampleAggregateRootId();

		$reflClass = new \ReflectionClass(SampleAggregateRoot::class);
		$sample    = $reflClass->newInstanceWithoutConstructor();

		$event = new SampleCreated($id, [ 'foo' => 'bar', ]);
		$event->setAggregateId($id);

		$eventStream = new EventStream(SampleAggregateRoot::class, $id->getUuid(), [ $event ]);

		$sample->loadFromEventStream($eventStream);

		self::assertSame([], $sample->pullDomainEvents());
		self::assertSame($id->getUuid(), $sample->getEventStreamId());
		self::assertTrue($sample->loadedFromEvents);
		self::assertEquals('bar', $sample->foo);
	}

	/**
	 * @test
	 */
	public function it_cannot_rehydrate_with_eventstream()
	{
		$id     = new SampleAggregateRootId();
		$sample = new SampleAggregateRoot($id);

		$eventStream = new EventStream(SampleAggregateRoot::class, $id->getUuid(), [ new SampleCreated($id, [ 'foo' => 'bar' ]) ]);

		self::expectException(RuntimeException::class);
		self::expectExceptionMessage('AggregateRoot was already created from event stream and cannot be hydrated again.');

		$sample->loadFromEventStream($eventStream);
	}
}
