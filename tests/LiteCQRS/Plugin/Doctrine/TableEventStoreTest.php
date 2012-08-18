<?php

namespace LiteCQRS\Plugin\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use LiteCQRS\Plugin\Doctrine\EventStore\TableEventStore;
use LiteCQRS\DomainObjectChanged;

class TableEventStoreTest extends \PHPUnit_Framework_TestCase
{
    public function testStoreEvent()
    {
        $serializer = $this->getMock('LiteCQRS\EventStore\SerializerInterface');
        $serializer->expects($this->once())->method('serialize')->will($this->returnValue('{}'));

        $conn = DriverManager::getConnection(array("driver" => "pdo_sqlite", "memory" => true));
        $eventStore = new TableEventStore($conn, $serializer);

        $schema = new Schema();
        $eventStore->addEventsToSchema($schema);
        $conn->getSchemaManager()->createTable($schema->getTable('litecqrs_events'));

        $event = new DomainObjectChanged("Test", array());

        $eventStore->store($event);

        $data = $conn->fetchAll('SELECT * FROM litecqrs_events');
        $this->assertEquals(1, count($data));
        $this->assertEquals($event->getMessageHeader()->id, $data[0]['event_id']);
        $this->assertEquals('{}', $data[0]['data']);
    }
}
