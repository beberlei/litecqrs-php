<?php

namespace LiteCQRS\CommandHandler;

use LiteCQRS\Command;

interface CommandHandlerInterface
{
    public function handle(Command $command);
}

