<?php

namespace LiteCQRS\Bus;

/**
 * Basic MessageHandler Proxy Factory that you can use in your application.
 */
abstract class MessageHandlerProxyFactory
{
    final public function __invoke($args)
    {
        return $this->proxyHandler($args[0]);
    }

    abstract protected function proxyHandler(MessageHandlerInterface $handler);
}

