<?php

namespace LiteCQRS\Plugin\Monolog;

use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\Bus\MessageInterface;
use LiteCQRS\DomainEvent;
use LiteCQRS\Command;
use Exception;

use Monolog\Logger;

/**
 * Logs all commands or events and their sucess/failure status
 * into a logger. The input data is json serialized during that process.
 */
class MonologDebugLogger implements MessageHandlerInterface
{
    private $logger;
    private $next;

    public function __construct(MessageHandlerInterface $next, Logger $logger)
    {
        $this->next   = $next;
        $this->logger = $logger;
    }

    public function handle(MessageInterface $message)
    {
        if ($message instanceof Command) {
            $parts = explode("\\", get_class($message));
            $log   = "Command[%s]: %s";
            $info  = end($parts);
        } else if ($message instanceof DomainEvent) {
            $log  = "Event[%s]: %s";
            $info = $message->getEventName() . "/" . $message->getMessageHeader()->id;
        }

        try {
            $this->logger->debug(sprintf($log, 'STARTING', $info));
            $this->next->handle($message);
            $this->logger->debug(sprintf($log, 'SUCCESS', $info));
        } catch(Exception $e) {
            $this->logger->err(sprintf($log, 'FAIL', $info) . ' - ' . $e->getMessage());
            throw $e;
        }
    }
}

