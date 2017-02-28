<?php

namespace LiteCQRS\Serializer;

use PHPUnit\Framework\TestCase;

class ReflectionSerializerTest extends TestCase
{

	/**
	 * @dataProvider dataObjectsAndTheirArrays
	 * @test
	 */
	public function it_serializes_objects_to_array($object, $expectedArray)
	{
		$serializer = new ReflectionSerializer();
		$this->assertEquals($expectedArray, $serializer->toArray($object));
	}

	/**
	 * @dataProvider dataObjectsAndTheirArrays
	 * @test
	 */
	public function it_unserializes_array_to_object_graph($expectedObjectGraph, $dataArray)
	{
		$serializer = new ReflectionSerializer();
		$this->assertEquals($expectedObjectGraph, $serializer->fromArray($dataArray));
	}

	static public function dataObjectsAndTheirArrays()
	{
		return [
			[
				new \DateTime('2013-11-02 18:38:29', new \DateTimeZone('Europe/Berlin')),
				[ 'time' => '2013-11-02 18:38:29.000000', 'timezone' => 'Europe/Berlin', 'php_class' => 'DateTime' ],
			],
			[
				$uuid = \Rhumsaa\Uuid\Uuid::uuid4(),
				[ 'php_class' => 'Rhumsaa\Uuid\Uuid', 'uuid' => (string) $uuid ],
			],
			[
				new DateRange(
					new \DateTime('2013-11-02 18:38:29', new \DateTimeZone('Europe/Berlin')),
					new \DateTime('2013-12-02 18:38:29', new \DateTimeZone('Europe/Berlin'))
				),
				[
					'start'     => [ 'time' => '2013-11-02 18:38:29.000000', 'timezone' => 'Europe/Berlin', 'php_class' => 'DateTime' ],
					'end'       => [ 'time' => '2013-12-02 18:38:29.000000', 'timezone' => 'Europe/Berlin', 'php_class' => 'DateTime' ],
					'php_class' => 'LiteCQRS\Serializer\DateRange',
				],
			],
			[
				new Person('Benjamin', new Address('Bonn', 'Germany')),
				[
					'name'      => 'Benjamin',
					'address'   => [
						'city'      => 'Bonn',
						'country'   => 'Germany',
						'php_class' => __NAMESPACE__ . '\\Address',
					],
					'php_class' => __NAMESPACE__ . '\\Person',
				],
			],
		];
	}
}
