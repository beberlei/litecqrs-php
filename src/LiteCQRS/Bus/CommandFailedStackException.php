<?php

namespace LiteCQRS\Bus;

use RuntimeException;

class CommandFailedStackException extends RuntimeException
{
    private $exceptions;
    public function __construct(array $exceptions)
    {
        $this->exceptions = $exceptions;
        $prevEx           = $exceptions[0]['ex']; // set the first exception as previous

        $message = "";
        foreach ($exceptions as $ex) {
            $parts = explode("\\", get_class($ex['command']));
            $message .= end($parts) . ": " . $ex['ex']->getMessage() . "\n";
        }

        parent::__construct(
            sprintf('During sequential execution %d commands failed to execute: %s', count($exceptions), $message),
            0,
            $prevEx
        );
    }

    public function getCommandExceptions()
    {
        return $this->exceptions;
    }
}

