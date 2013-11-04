<?php

namespace LiteCQRS\Bus;

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
abstract class SequentialCommandBus implements CommandBus
{
    /**
     * @var callable[]
     */
    private $proxyFactories;
    private $commandStack = array();
    private $exceptionStack = array();
    private $executing = false;

    public function __construct(array $proxyFactories = array())
    {
        $this->proxyFactories = $proxyFactories;
    }

    /**
     * Given a Command Type (ClassName) return an instance of
     * the service that is handling this command.
     *
     * @param string $commandType A Command Class name
     * @return object
     */
    abstract protected function getService($commandType);

    /**
     * Sequentially execute commands
     *
     * Only exceptions occurring in the first command will be thrown.
     *
     * @param object $command
     * @throws \Exception
     */
    public function handle($command)
    {
        $this->commandStack[] = $command;

        if ($this->executing) {
            return;
        }

        $first = true;


        while ($command = array_shift($this->commandStack)) {
            try {
                $this->executing = true;
                $type = get_class($command);
                $service = $this->getService($type);
                $handler = new CommandInvocationHandler($service);
                $handler = $this->proxyHandler($handler);

                $handler->handle($command);
            } catch (\Exception $e) {
                $this->executing = false;
                $this->handleException($e, $first);
            }

            $this->executing = false;
            $first = false;
        }
    }

    /**
     * @param \Exception $e
     * @param boolean $first
     * @throws \Exception
     */
    protected function handleException($e, $first)
    {
        if ($first) {
            throw $e;
        }
        $this->exceptionStack[] = $e;
    }

    protected function proxyHandler($handler)
    {
        foreach (array_reverse($this->proxyFactories) as $proxyFactory) {
            $handler = $proxyFactory($handler);
        }
        return $handler;
    }
}
