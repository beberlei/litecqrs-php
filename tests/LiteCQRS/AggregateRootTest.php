<?php

namespace LiteCQRS;

use Rhumsaa\Uuid\Uuid;
use LiteCQRS\EventStore\EventStream;

class AggregateRootTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_manages_id()
    {
        $uuid = Uuid::uuid4();
        $sample = new SampleAggregateRoot($uuid);

        $this->assertSame($uuid, $sample->getId());
    }

    /**
     * @test
     */
    public function it_calls_apply_methods_for_events()
    {
        $uuid = Uuid::uuid4();
        $sample = new SampleAggregateRoot($uuid);

        $this->assertTrue($sample->loadedFromEvents);
        $this->assertEquals('bar', $sample->foo);
    }

    /**
     * @test
     */
    public function it_hydrates_from_eventstream()
    {
        $uuid = Uuid::uuid4();

        $reflClass = new \ReflectionClass('LiteCQRS\SampleAggregateRoot');
        $sample = $reflClass->newInstanceWithoutConstructor();

        $events = array(new SampleCreated(array('foo' => 'bar')));

        $eventStream = new EventStream('LiteCQRS\SampleAggregateRoot', $uuid, $events);

        $sample->loadFromEventStream($eventStream);

        $this->assertSame(array(), $sample->pullDomainEvents());
        $this->assertSame($uuid, $sample->getId());
        $this->assertTrue($sample->loadedFromEvents);
        $this->assertEquals('bar', $sample->foo);
    }

    /**
     * @test
     */
    public function it_cannot_rehydrate_with_eventstream()
    {
        $uuid = Uuid::uuid4();
        $sample = new SampleAggregateRoot($uuid);

        $eventStream = new EventStream('LiteCQRS\SampleAggregateRoot', $uuid, array(new SampleCreated(array('foo' => 'bar'))));

        $this->setExpectedException('LiteCQRS\Exception\RuntimeException', 'AggregateRoot was already created from event stream and cannot be hydrated again.');

        $sample->loadFromEventStream($eventStream);
    }
}

class SampleAggregateRoot extends AggregateRoot
{
    public $loadedFromEvents = false;
    public $foo;

    public function __construct(Uuid $uuid)
    {
        $this->setId($uuid);

        $this->apply(new SampleCreated(array('foo' => 'bar')));
    }

    public function applySampleCreated(SampleCreated $event)
    {
        $this->foo = $event->foo;
        $this->loadedFromEvents = true;
    }
}

class SampleCreated extends DefaultDomainEvent
{
    public $foo;
}
