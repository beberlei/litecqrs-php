<?php

namespace LiteCQRS;

class DefaultCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateArrayMapsToPublicProperties()
    {
        $cmd = new TestCommand(Array("test" => "value"));

        $this->assertEquals("value", $cmd->test);
    }

    public function testCreateThrowsExceptionWhenUnknownPropertySet()
    {
        $this->setExpectedException('RuntimeException', 'Property unknown is not a valid property on command Test');
        $cmd = new TestCommand(Array("unknown" => "value"));
    }
}

class TestCommand extends DefaultCommand
{
    public $test;
}
