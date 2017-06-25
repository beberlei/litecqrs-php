<?php

namespace LidskaSila\Glow\Commanding;

/**
 * Accept and process commands by passing them along to a matching command handler.
 */
interface CommandBus
{

	public function handle(Command $command);
}

