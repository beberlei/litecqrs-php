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
		if (!isset($data['php_class'])) {
			return $this->fromArrayToArrayObjects($data);
		}

		return $this->fromArrayToObject($data);
	}

	private function fromArrayToArrayObjects(array $data)
	{
		$newData = [];
		foreach ($data as $key => $value) {
			if (is_array($value) || is_object($value)) {
				$newData[$key] = $this->fromArray($value);
			} else {
				$newData[$key] = $value;
			}
		}

		return $newData;
	}

	private function fromArrayToObject($data)
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

	public function toArray($value)
	{
		if (is_object($value)) {
			return $this->fromObjectToArray($value);
		} elseif (is_array($value)) {
			return $this->fromArrayToArray($value);
		} else {
			return $value;
		}
	}

	/**
	 * @param $object
	 *
	 * @return array
	 */
	private function fromObjectToArray($object): array
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
			$reflField->setAccessible(true);
			$value = $reflField->getValue($object);

			if (is_object($value) || is_array($value)) {
				$value = $this->toArray($value);
			}
			$data[$reflField->getName()] = $value;
		}

		return $data;
	}

	/**
	 * @param array $array
	 *
	 * @return array
	 */
	private function fromArrayToArray($array): array
	{
		$newData = [];
		foreach ($array as $key => $value) {
			$newData[$key] = $this->toArray($value);
		}

		return $newData;
	}
}
