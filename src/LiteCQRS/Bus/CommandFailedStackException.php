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

        parent::__construct(
            sprintf('During sequential execution %d commands failed to execute.', count($exceptions)),
            0,
            $prevEx
        );
    }

    public function getCommandExceptions()
    {
        return $this->exceptions;
    }
}

