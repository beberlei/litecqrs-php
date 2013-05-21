<?php

namespace LiteCQRS\Plugin\Doctrine\EventStore;

use LiteCQRS\DomainObjectChanged;
use LiteCQRS\Plugin\Doctrine\EventStore\CouchDBEventStore;
use Doctrine\CouchDB\CouchDBClient;

class CouchDBEventStoreTest extends \PHPUnit_Framework_TestCase
{
    public function testStore()
    {
        $serializer = $this->getMock('LiteCQRS\EventStore\SerializerInterface');
        $serializer->expects($this->once())->method('serialize')->will($this->returnValue('{}'));

        $couch = CouchDBClient::create(array(
            'dbname' => 'litecqrs_tests',
            'type'   => 'socket',
        ));
        try {
            $couch->createDatabase('litecqrs_tests');
        } catch(\Exception $e) {
        }

        $eventStore = new CouchDBEventStore($couch, $serializer);

        $event = new DomainObjectChanged("Test", array());

        $eventStore->store($event);

        $response = $couch->findDocument($event->getMessageHeader()->id);
        $this->assertEquals("Test", $response->body['event']);
    }
}
