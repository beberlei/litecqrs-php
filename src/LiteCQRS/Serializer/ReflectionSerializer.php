<?php

namespace LiteCQRS\Serializer;

use Ramsey\Uuid\Uuid;
use ReflectionClass;

use DateTime;
use DateTimeInterface;
use DateTimeImmutable;
use DateTimeZone;

class ReflectionSerializer implements Serializer
{
    /**
     * @var array
     */
    private $classes = array();

    /**
     * @var array
     */
    private $fields = array();

    public function fromArray(array $data)
    {
        if ($data['php_class'] === "DateTime") {
            return DateTime::createFromFormat('Y-m-d H:i:s.u', $data['time'], new DateTimeZone($data['timezone']));
        }

        if ($data['php_class'] === "DateTimeImmutable") {
            return DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $data['time'], new DateTimeZone($data['timezone']));
        }

        if ($data['php_class'] === "Ramsey\Uuid\Uuid") {
            return Uuid::fromString($data['uuid']);
        }

        $reflClass = $this->getReflectionClass($data['php_class']);
        $constructor = $reflClass->getConstructor();

        $arguments = array();

        foreach ($constructor->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass();
            $parameterName = strtolower($parameter->getName());

            if ($parameterClass !== null && isset($data[$parameterName])) {
                $data[$parameterName] = $this->fromArray($data[$parameterName]);
            }

            $arguments[] = isset($data[$parameterName])
                ? $data[$parameterName]
                : $parameter->getDefaultValue();
        }

        return $reflClass->newInstanceArgs($arguments);
    }

    public function toArray($object)
    {
        if ($object instanceof DateTime || $object instanceof DateTimeInterface) {
            return array(
                'php_class' => get_class($object),
                'time' => $object->format('Y-m-d H:i:s.u'),
                'timezone' => $object->getTimezone()->getName()
            );
        }

        if ($object instanceof Uuid) {
            return array(
                'php_class' => 'Ramsey\Uuid\Uuid',
                'uuid' => (string)$object,
            );
        }

        return $this->extractValuesFromObject($object);
    }

    private function extractValuesFromObject($object)
    {
        $reflClass = $this->getReflectionClass(get_class($object));
        $constructor = $reflClass->getConstructor();

        $data = array(
            'php_class' => get_class($object),
        );

        foreach ($constructor->getParameters() as $parameter) {
            $reflField = $this->getReflectionField($reflClass, $parameter->getName());

            $value = $reflField->getValue($object);

            if (is_object($value)) {
                $value = $this->toArray($value);
            }

            $data[strtolower($parameter->getName())] = $value;
        }

        return $data;
    }

    /**
     * @param string $className
     * @return ReflectionClass
     */
    private function getReflectionClass($className)
    {
        if (!isset($this->classes[$className])) {
            $this->classes[$className] = new ReflectionClass($className);
        }

        return $this->classes[$className];
    }

    /**
     * @return ReflectionProperty
     */
    private function getReflectionField(ReflectionClass $reflectionClass, $propertyName)
    {
        if (!isset($this->fields[$reflectionClass->getName()][$propertyName])) {
            $reflField = $reflectionClass->getProperty($propertyName);
            $reflField->setAccessible(true);

            $this->fields[$reflectionClass->getName()][$propertyName] = $reflField;
        }

        return $this->fields[$reflectionClass->getName()][$propertyName];
    }
}
