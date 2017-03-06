<?php

namespace LidskaSila\Glow\Commanding;

interface CommandHandler
{

	public function handle(Command $command);
}
