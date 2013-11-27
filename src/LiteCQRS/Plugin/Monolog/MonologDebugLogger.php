<?php

namespace LiteCQRS\Plugin\Monolog;

use LiteCQRS\Bus\MessageHandlerInterface;
use LiteCQRS\DomainEvent;
use LiteCQRS\Command;
use Exception;

use Monolog\Logger;

/**
 * Logs all commands or events and their success/failure status
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

    public function handle($message)
    {
        if ($message instanceof Command) {
            $parts   = explode("\\", get_class($message));
            $log     = "Command[%s]: %s";
            $info    = end($parts);
            $context = array();
        } else if ($message instanceof DomainEvent) {
            $log     = "Event[%s]: %s";
            $info    = $message->getEventName();
            $context = array('aggregate_id' => $message->getAggregateId());
        } else {
            $parts   = explode("\\", get_class($message));
            $log = "Message[%s]: %s";
            $info    = end($parts);
            $context = array();
        }

        try {
            $this->logger->debug(sprintf($log, 'STARTING', $info), $context);
            $this->next->handle($message);
            $this->logger->debug(sprintf($log, 'SUCCESS', $info), $context);
        } catch(Exception $e) {
            $this->logger->err(sprintf($log, 'FAIL', $info) . ' - ' . $e->getMessage(), $context);
            throw $e;
        }
    }
}

