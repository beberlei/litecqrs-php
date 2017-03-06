<?php

namespace LidskaSila\Glow;

use LidskaSila\Glow\EventStore\EventStream;
use LidskaSila\Glow\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class AggregateRootTest extends TestCase
{

	/**
	 * @test
	 */
	public function it_manages_id()
	{
		$uuid   = Uuid::uuid4();
		$sample = new SampleAggregateRoot($uuid);

		self::assertSame($uuid, $sample->getId());
	}

	/**
	 * @test
	 */
	public function it_calls_apply_methods_for_events()
	{
		$uuid   = Uuid::uuid4();
		$sample = new SampleAggregateRoot($uuid);

		self::assertTrue($sample->loadedFromEvents);
		self::assertEquals('bar', $sample->foo);
	}

	/**
	 * @test
	 */
	public function it_hydrates_from_eventstream()
	{
		$uuid = Uuid::uuid4();

		$reflClass = new \ReflectionClass(SampleAggregateRoot::class);
		$sample    = $reflClass->newInstanceWithoutConstructor();

		$events = [ new SampleCreated([ 'foo' => 'bar' ]) ];

		$eventStream = new EventStream(SampleAggregateRoot::class, $uuid, $events);

		$sample->loadFromEventStream($eventStream);

		self::assertSame([], $sample->pullDomainEvents());
		self::assertSame($uuid, $sample->getId());
		self::assertTrue($sample->loadedFromEvents);
		self::assertEquals('bar', $sample->foo);
	}

	/**
	 * @test
	 */
	public function it_cannot_rehydrate_with_eventstream()
	{
		$uuid   = Uuid::uuid4();
		$sample = new SampleAggregateRoot($uuid);

		$eventStream = new EventStream(SampleAggregateRoot::class, $uuid, [ new SampleCreated([ 'foo' => 'bar' ]) ]);

		self::expectException(RuntimeException::class);
		self::expectExceptionMessage('AggregateRoot was already created from event stream and cannot be hydrated again.');

		$sample->loadFromEventStream($eventStream);
	}
}
