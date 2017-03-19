<?php

namespace LidskaSila\Glow\Serializer;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use ReflectionProperty;

class ReflectionSerializer implements Serializer
{

	public function fromArray(array $data)
	{
		$className = $data['php_class'];
		if ($className === DateTime::class) {
			return DateTime::createFromFormat('Y-m-d H:i:s.u', $data['time'], new DateTimeZone($data['timezone']));
		}

		if ($className === DateTimeImmutable::class) {
			return DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $data['time'], new DateTimeZone($data['timezone']));
		}

		if ($className === Uuid::class) {
			return Uuid::fromString($data['uuid']);
		}

		$reflectionClass = $this->getReflectionClass($className);
		$object          = $reflectionClass->newInstanceWithoutConstructor();

		foreach ($this->getProperties($className) as $fieldName => $reflField) {

			if (empty($data[$fieldName])) {
				continue;
			}
			if (is_array($data[$fieldName])) {
				$value = $this->fromArray($data[$fieldName]);
			} else {
				$value = $data[$fieldName];
			}
			$reflField->setValue($object, $value);
		}

		return $object;
	}

	public function toArray($object)
	{
		if ($object instanceof DateTime || $object instanceof DateTimeInterface) {
			return [
				'php_class' => get_class($object),
				'time'      => $object->format('Y-m-d H:i:s.u'),
				'timezone'  => $object->getTimezone()->getName(),
			];
		}

		if ($object instanceof Uuid) {
			return [
				'php_class' => 'Ramsey\Uuid\Uuid',
				'uuid'      => (string) $object,
			];
		}

		return $this->extractValuesFromObject($object);
	}

	private function extractValuesFromObject($object)
	{
		$data = [
			'php_class' => get_class($object),
		];

		foreach ($this->getProperties(get_class($object)) as $reflField) {

			$value = $reflField->getValue($object);

			if (is_object($value)) {
				$value = $this->toArray($value);
			}

			$data[$reflField->getName()] = $value;
		}

		return $data;
	}

	/**
	 * @param string $className
	 *
	 * @return ReflectionProperty[]
	 */
	private function getProperties(string $className): array
	{
		$properties = [];
		try {
			$rc = new \ReflectionClass($className);
			do {
				$rp = [];
				/* @var $p ReflectionProperty */
				foreach ($rc->getProperties() as $p) {
					$p->setAccessible(true);
					$rp[$p->getName()] = $p;
				}
				$properties = array_merge($rp, $properties);
				$rc         = $rc->getParentClass();
			} while ($rc);
		} catch (\ReflectionException $e) {
			/**/
		}

		return $properties;
	}

	/**
	 * @param string $className
	 *
	 * @return ReflectionClass
	 *
	 * @throws InvalidArgumentException
	 */
	private function getReflectionClass($className)
	{
		if (!class_exists($className)) {
			throw InvalidArgumentException::fromNonExistingClass($className);
		}

		$reflection = new ReflectionClass($className);

		if ($reflection->isAbstract()) {
			throw InvalidArgumentException::fromAbstractClass($reflection);
		}

		return $reflection;
	}
}
