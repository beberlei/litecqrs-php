<?php

namespace LiteCQRS;

class DefaultDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayToProperties()
    {
        $event = new TestEvent(array("test" => "value"));

        $this->assertEquals("value", $event->test);
    }

    public function testWrongPropertyThrowsException()
    {
        $this->setExpectedException("RuntimeException", "Property unknown is not a valid property on event Test");
        $event = new TestEvent(array("unknown" => "value"));
    }

    public function testGetEventName()
    {
        $event = new TestEvent(array("test" => "value"));

        $this->assertEquals("Test", $event->getEventName());
    }
}

class TestEvent extends DefaultDomainEvent
{
    public $test;
}
