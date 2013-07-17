<?php

namespace LiteCQRS\Plugin\JMSSerializer;

use JMS\SerializerBundle\Serializer\Handler\DeserializationHandlerInterface;
use JMS\SerializerBundle\Serializer\Handler\SerializationHandlerInterface;
use JMS\SerializerBundle\Serializer\VisitorInterface;
use JMS\SerializerBundle\Serializer\XmlDeserializationVisitor;
use JMS\SerializerBundle\Serializer\Construction\UnserializeObjectConstructor;
use JMS\SerializerBundle\Serializer\JsonDeserializationVisitor;
use JMS\SerializerBundle\Serializer\Handler\ObjectBasedCustomHandler;
use JMS\SerializerBundle\Serializer\Handler\DateTimeHandler;
use JMS\SerializerBundle\Serializer\Handler\ArrayCollectionHandler;
use JMS\SerializerBundle\Serializer\Handler\DoctrineProxyHandler;
use JMS\SerializerBundle\Serializer\XmlSerializationVisitor;
use JMS\SerializerBundle\Metadata\Driver\AnnotationDriver;
use JMS\SerializerBundle\Annotation AS JMS;
use JMS\SerializerBundle\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\SerializerBundle\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\SerializerBundle\Serializer\JsonSerializationVisitor;
use JMS\SerializerBundle\Serializer\Serializer;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Metadata\MetadataFactory;

use LiteCQRS\Plugin\JMSSerializer\AggregateRootHandler;
use LiteCQRS\Plugin\JMSSerializer\JMSSerializer;

use LiteCQRS\DefaultDomainEvent;
use LiteCQRS\AggregateRoot;
use LiteCQRS\DomainEventProviderRepositoryInterface;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeEvent()
    {
        $identityMap = $this->getMock('LiteCQRS\Bus\IdentityMap\IdentityMapInterface');
        $repository = $this->getMock('LiteCQRS\DomainEventProviderRepositoryInterface');
        $serializer = $this->createJmsSerializer($identityMap, $repository);

        $event = new SomeEvent(array("root" => new SomeAggregateRoot()));
        $data = $serializer->serialize($event, "json");

        $this->assertEquals('{"root":{"aggregate_type":"LiteCQRS\\\\Plugin\\\\JMSSerializer\\\\SomeAggregateRoot","aggregate_id":null}}', $data);
    }

    public function testDeserializeEvent()
    {
        $data = '{"root":{"aggregate_type":"LiteCQRS\\\\Plugin\\\\JMSSerializer\\\\SomeAggregateRoot","aggregate_id":1}}';

        $identityMap = $this->getMock('LiteCQRS\Bus\IdentityMap\IdentityMapInterface');
        $repository = $this->getMock('LiteCQRS\DomainEventProviderRepositoryInterface');
        $repository->expects($this->once())
                   ->method('find')
                   ->with($this->equalTo('LiteCQRS\Plugin\JMSSerializer\SomeAggregateRoot'), $this->equalTo(1))
                   ->will($this->returnValue($ar = new SomeAggregateRoot()));

        $serializer = $this->createJmsSerializer($identityMap, $repository);

        $event = $serializer->deserialize($data, 'LiteCQRS\Plugin\JMSSerializer\SomeEvent', 'json');

        $this->assertSame($event->root, $ar);
    }

    public function createJmsSerializer($identityMap, $repository)
    {
        $namingStrategy    = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
        $objectConstructor = new UnserializeObjectConstructor();
        $arHandler = new AggregateRootHandler($identityMap, $repository);

        $customSerializationHandlers = array(
            new DateTimeHandler(),
            new DoctrineProxyHandler(),
            $arHandler
        );

        $customDeserializationHandlers = array(
            new DateTimeHandler(),
            new ArrayCollectionHandler(),
            $arHandler
        );

        $serializationVisitors = array(
            'json' => new JsonSerializationVisitor($namingStrategy, $customSerializationHandlers),
            'xml'  => new XmlSerializationVisitor($namingStrategy, $customSerializationHandlers),
        );
        $deserializationVisitors = array(
            'json' => new JsonDeserializationVisitor($namingStrategy, $customDeserializationHandlers, $objectConstructor),
            'xml'  => new XmlDeserializationVisitor($namingStrategy, $customDeserializationHandlers, $objectConstructor),
        );

        $factory = $this->createJmsMetadataFactory();
        return new JMSSerializer(
            new Serializer($factory, $serializationVisitors, $deserializationVisitors),
            $arHandler
        );
    }

    public function createJmsMetadataFactory()
    {
        $fileLocator = new \Metadata\Driver\FileLocator(array());
        $driver      = new \Metadata\Driver\DriverChain(array(
            new \JMS\SerializerBundle\Metadata\Driver\YamlDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\XmlDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\PhpDriver($fileLocator),
            new \JMS\SerializerBundle\Metadata\Driver\AnnotationDriver(new \Doctrine\Common\Annotations\AnnotationReader())
        ));
        return new MetadataFactory($driver);
    }

}

class SomeEvent extends DefaultDomainEvent
{
    /**
     * @JMS\Type("LiteCQRS\Plugin\JMSSerializer\SomeAggregateRoot")
     */
    public $root;
}

class SomeAggregateRoot extends AggregateRoot
{
    public $foo = "bar";
}
