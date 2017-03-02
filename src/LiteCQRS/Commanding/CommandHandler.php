<?php

namespace LiteCQRS\Commanding;

interface CommandHandler
{

	public function handle(Command $command);
}
