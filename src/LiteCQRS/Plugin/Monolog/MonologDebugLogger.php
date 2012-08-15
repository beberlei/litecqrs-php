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
            $log   = "Command[%s]: ";
            $info  = substr(end($parts) . ": " . json_encode($message), 0, 4000);
        } else if ($message instanceof DomainEvent) {
            $log  = "Event[%s]: ";
            $info = substr($message->getEventName() . ": " . json_encode($message), 0, 4000);
        }

        try {
            $this->logger->debug(sprintf($log, 'STARTING') . $info);
            $this->next->handle($message);
            $this->logger->err(sprintf($log, 'SUCCESS') . $info);
        } catch(Exception $e) {
            $this->logger->err(sprintf($log, 'FAIL') . ' - ' . $e->getMessage());
            throw $e;
        }
    }
}

