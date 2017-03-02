<?php

namespace LiteCQRS\Commanding;

class MemoryCommandHandlerLocator implements CommandHandlerLocator
{

	/** @var CommandHandler[] */
	private $handlers = [];

	public function getCommandHandler(Command $command)
	{
		$commandHandlerKey = $this->getKeyFromCommand($command);
		if (!isset($this->handlers[$commandHandlerKey])) {
			throw new \RuntimeException("No service registered for command type '" . $commandHandlerKey . "'. You need to register Command handler!");
		}

		return $this->handlers[$commandHandlerKey];
	}

	public function register(CommandHandler $commandHandler)
	{
		$this->handlers[self::getKeyFromCommandHandler($commandHandler)] = $commandHandler;
	}

	private function getKeyFromCommand(Command $command)
	{
		return self::getShortObjectName($command);
	}

	private static function getKeyFromCommandHandler(CommandHandler $commandHandler)
	{
		$className = self::getShortObjectName($commandHandler);

		return substr($className, 0, -strlen('Handler'));
	}

	private static function getShortObjectName($object)
	{
		return (new \ReflectionClass($object))->getShortName();
	}
}
