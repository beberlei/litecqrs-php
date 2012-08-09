<?php

namespace LiteCQRS\CommandHandler;

use LiteCQRS\Command;

interface CommandHandler
{
    public function handle(Command $command);
}

