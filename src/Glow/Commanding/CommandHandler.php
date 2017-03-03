<?php

namespace Lidskasila\Glow\Commanding;

interface CommandHandler
{

	public function handle(Command $command);
}
