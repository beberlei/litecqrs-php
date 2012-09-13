<?php

namespace LiteCQRS\Plugin\Doctrine;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use LiteCQRS\Plugin\Doctrine\EventStore\TableEventStore,
    LiteCQRS\Plugin\Doctrine\EventStore\TableEventStoreSchema;
use LiteCQRS\DomainObjectChanged;

class TableEventStoreTest extends \PHPUnit_Framework_TestCase
{
    public function testStoreEvent()
    {
        $serializer = $this->getMock('LiteCQRS\EventStore\SerializerInterface');
        $serializer->expects($this->once())->method('serialize')->will($this->returnValue('{}'));

        $conn = DriverManager::getConnection(array("driver" => "pdo_sqlite", "memory" => true));

        $schema = new TableEventStoreSchema();
        $tableSchema = $schema->getTableSchema();
        $conn->getSchemaManager()->createTable($tableSchema);

        $eventStore = new TableEventStore($conn, $serializer, $tableSchema->getName());

        $event = new DomainObjectChanged("Test", array());

        $eventStore->store($event);

        $data = $conn->fetchAll('SELECT * FROM litecqrs_events');
        $this->assertEquals(1, count($data));
        $this->assertEquals($event->getMessageHeader()->id, $data[0]['event_id']);
        $this->assertEquals('{}', $data[0]['data']);
    }
}
