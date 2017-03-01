<?php

namespace LiteCQRS;

/**
 * Default Implementation for the Command interface.
 *
 * Convenience Command that helps with construction by mapping an array input
 * to command properties. If a passed property does not exist on the class
 * an exception is thrown.
 *
 * @example
 *
 *   class GreetCommand extends DefaultCommand
 *   {
 *      public $personId;
 *   }
 *   $command = new GreetCommand(array("personId" => 1));
 *   $commandBus->handle($command);
 */
abstract class DefaultCommand implements Command
{

	public function __construct(array $data = [])
	{
		foreach ($data as $key => $value) {
			if (!property_exists($this, $key)) {
				$parts   = explode("\\", get_class($this));
				$command = str_replace("Command", "", end($parts));

				throw new \RuntimeException(sprintf('Property "%s" is not a valid property on command "%s".', $key, $command));
			}

			$this->$key = $value;
		}
	}
}

