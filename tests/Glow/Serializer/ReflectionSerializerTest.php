<?php

namespace LidskaSila\Glow\Serializer;

use PHPUnit\Framework\TestCase;

class ReflectionSerializerTest extends TestCase
{

	static public function dataObjectsAndTheirArrays()
	{
		return [
			[
				new \DateTime('2013-11-02 18:38:29', new \DateTimeZone('Europe/Berlin')),
				[ 'time' => '2013-11-02 18:38:29.000000', 'timezone' => 'Europe/Berlin', 'php_class' => 'DateTime' ],
			],
			[
				$uuid = \Ramsey\Uuid\Uuid::uuid4(),
				[ 'php_class' => 'Ramsey\Uuid\Uuid', 'uuid' => (string) $uuid ],
			],
			[
				new DateRange(
					new \DateTime('2013-11-02 18:38:29', new \DateTimeZone('Europe/Berlin')),
					new \DateTime('2013-12-02 18:38:29', new \DateTimeZone('Europe/Berlin'))
				),
				[
					'start'     => [ 'time' => '2013-11-02 18:38:29.000000', 'timezone' => 'Europe/Berlin', 'php_class' => 'DateTime' ],
					'end'       => [ 'time' => '2013-12-02 18:38:29.000000', 'timezone' => 'Europe/Berlin', 'php_class' => 'DateTime' ],
					'php_class' => 'LidskaSila\Glow\Serializer\DateRange',
				],
			],
			[
				new Person('Benjamin', new Address('Bonn', 'Germany')),
				[
					'name'      => 'Benjamin',
					'address'   => [
						'city'      => 'Bonn',
						'country'   => 'Germany',
						'php_class' => Address::class,
					],
					'php_class' => Person::class,
				],
			],
			[
				new ClassWithArrayAsProperty('Czechia', [ new Address('Bonn', 'Germany'), new Address('Prague', 'Czechia') ]),
				[
					'country'         => 'Czechia',
					'detailWithArray' => [
						[
							'city'      => 'Bonn',
							'country'   => 'Germany',
							'php_class' => Address::class,
						],
						[
							'city'      => 'Prague',
							'country'   => 'Czechia',
							'php_class' => Address::class,
						],
					],
					'php_class'       => ClassWithArrayAsProperty::class,
				],
			],
		];
	}

	/**
	 * @dataProvider dataObjectsAndTheirArrays
	 * @test
	 */
	public function it_serializes_objects_to_array($object, $expectedArray)
	{
		$serializer = new ReflectionSerializer();
		self::assertEquals($expectedArray, $serializer->toArray($object));
	}

	/**
	 * @dataProvider dataObjectsAndTheirArrays
	 * @test
	 */
	public function it_unserializes_array_to_object_graph($expectedObjectGraph, $dataArray)
	{
		$serializer = new ReflectionSerializer();
		self::assertEquals($expectedObjectGraph, $serializer->fromArray($dataArray));
	}
}
