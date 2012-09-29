<?php

namespace LiteCQRS\Plugin\DoctrineMongoDB;

use Doctrine\MongoDB\Connection;
use LiteCQRS\DomainObjectChanged;
use LiteCQRS\Plugin\DoctrineMongoDB\EventStore\MongoDBEventStore;

class MongoDBEventStoreTest extends \PHPUnit_Framework_TestCase
{
    public function testStore()
    {
        $serializer = $this->getMock('LiteCQRS\EventStore\SerializerInterface');
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue('{}'))
        ;

        $connection = new Connection();
        $collection = $connection->selectCollection('litecqrs_tests', 'litecqrs_events');
        $eventStore = new MongoDBEventStore($connection, $serializer, 'litecqrs_tests', 'litecqrs_events');
        $event      = new DomainObjectChanged("Test", array());

        $eventStore->store($event);

        $document = $collection->findOne(array(
            'event_id' => $event->getMessageHeader()->id,
        ));

        $this->assertInternalType('array', $document);
        $this->assertEquals('Test', $document['event']);
    }
}
