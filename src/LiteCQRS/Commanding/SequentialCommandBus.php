<?php

namespace LiteCQRS\Commanding;

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
	 * @param object $command
	 *
	 * @throws CommandFailedStackException
	 */
	public function handle($command)
	{
		$this->commandStack[] = $command;

		if ($this->executing) {
			return;
		}

		$first = true;

		while ($command = array_shift($this->commandStack)) {
			$this->invokeHandler($command, $first);
			$first = false;
		}
	}

	protected function invokeHandler($command, $first)
	{
		try {
			$this->executing = true;

			$service = $this->locator->getCommandHandler($command);
			$method  = $this->getHandlerMethodName($command);

			if (!method_exists($service, $method)) {
				throw new \RuntimeException("Service " . get_class($service) . " has no method " . $method . " to handle command.");
			}

			$service->$method($command);
		} catch (\Exception $e) {
			$this->executing = false;
			$this->handleException($e, $first);
		}

		$this->executing = false;
	}

	protected function getHandlerMethodName($command)
	{
		$parts = explode("\\", get_class($command));

		return str_replace("Command", "", lcfirst(end($parts)));
	}

	protected function handleException($e, $first)
	{
		if ($first) {
			throw $e;
		}
	}
}

