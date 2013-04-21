<?php

namespace LiteCQRS\Exception;

use RuntimeException;

class CommandFailedStackException extends RuntimeException
{
    /**
     * @var \Exception[]
     */
    public $exceptionStack;

    public function __construct(array $exceptionStack)
    {
        $this->exceptionStack = $exceptionStack;
        parent::__construct('Command execution failed.');
    }
}