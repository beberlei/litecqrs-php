<?php

namespace LidskaSila\Glow\Serializer;

use ReflectionClass;

/**
 * Exception for invalid arguments provided to the instantiator
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class InvalidArgumentException extends \InvalidArgumentException
{

	/**
	 * @param string $className
	 *
	 * @return self
	 */
	public static function fromNonExistingClass($className)
	{
		if (interface_exists($className)) {
			return new self(sprintf('The provided type "%s" is an interface, and can not be instantiated', $className));
		}

		if (PHP_VERSION_ID >= 50400 && trait_exists($className)) {
			return new self(sprintf('The provided type "%s" is a trait, and can not be instantiated', $className));
		}

		return new self(sprintf('The provided class "%s" does not exist', $className));
	}

	/**
	 * @param ReflectionClass $reflectionClass
	 *
	 * @return self
	 */
	public static function fromAbstractClass(ReflectionClass $reflectionClass)
	{
		return new self(sprintf(
			'The provided class "%s" is abstract, and can not be instantiated',
			$reflectionClass->getName()
		));
	}
}
