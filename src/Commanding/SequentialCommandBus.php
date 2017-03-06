<?php

namespace LidskaSila\Glow\Commanding;

use Exception;

/**
 * Process Commands and pass them to their handlers in sequential order.
 *
 * If commands are triggered within command handlers, this command bus puts
 * them on a stack and waits with the execution to allow sequential processing
 * and avoiding nested transactions.
 *
 * Any command handler execution can be wrapped by additional handlers to form
 * a chain of responsibility. To control this process you can pass an array of
 * proxy factories into the CommandBus. The factories are iterated in REVERSE
 * order and get passed the current handler to stack the chain of
 * responsibility. That means the proxy factory registered FIRST is the one
 * that wraps itself around the previous handlers LAST.
 */
class SequentialCommandBus implements CommandBus
{

	private $locator;

	private $commandStack = [];

	private $executing    = false;

	public function __construct(CommandHandlerLocator $locator)
	{
		$this->locator = $locator;
	}

	/**
	 * Sequentially execute commands
	 *
	 * If an exception occurs in any command it will be put on a stack
	 * of exceptions that is thrown only when all the commands are processed.
	 *
	 * @param Command $command
	 *
	 * @throws Exception
	 */
	public function handle(Command $command)
	{
		$this->commandStack[] = $command;

		if ($this->executing) {
			return;
		}

		$first = true;

		while (count($this->commandStack)) {
			$command = array_shift($this->commandStack);
			$this->invokeHandler($command, $first);
			$first = false;
		}
	}

	protected function invokeHandler($command, $first)
	{
		try {
			$this->executing = true;

			$commandHandler = $this->locator->getCommandHandler($command);

			$commandHandler->handle($command);
		} catch (Exception $e) {
			$this->executing = false;
			$this->handleException($e, $first);
		}

		$this->executing = false;
	}

	protected function handleException($e, $first)
	{
		if ($first) {
			throw $e;
		}
	}
}

