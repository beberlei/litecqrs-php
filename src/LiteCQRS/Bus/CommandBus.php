<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Command;

/**
 * Process Commands and pass them to their handlers.
 *
 * Any command handler execution can be wrapped by additional handlers to form
 * a chain of responsibility. To control this process you can pass an array of
 * proxy factories into the CommandBus. The factories are iterated in REVERSE
 * order and get passed the current handler to stack the chain of
 * responsibility. That means the proxy factory registered FIRST is the one
 * that wraps itself around the previous handlers LAST.
 */
abstract class CommandBus
{
    /**
     * @var callable[]
     */
    private $proxyFactories;
    private $commandStack = array();

    public function __construct(array $proxyFactories = array())
    {
        $this->proxyFactories = $proxyFactories;
    }

    /**
     * Given a Commmand Type (ClassName) return an instance of
     * the service that is handling this command.
     *
     * @param string $commandType A Command Class name
     * @return object
     */
    abstract protected function getService($commandType);

    public function handle(Command $command)
    {
        $this->commandStack[] = $command;

        if (count($this->commandStack) > 1) {
            return;
        }

        while ($command = array_shift($this->commandStack)) {
            $type    = get_class($command);
            $service = $this->getService($type);
            $handler = new CommandInvocationHandler($service);
            $handler = $this->proxyHandler($handler);

            try {
                $handler->handle($command);
            } catch(\Exception $e) {
                throw $e;
            }
        }
    }

    protected function proxyHandler($handler)
    {
        foreach (array_reverse($this->proxyFactories) as $proxyFactory) {
            $handler = $proxyFactory($handler);
        }
        return $handler;
    }
}

