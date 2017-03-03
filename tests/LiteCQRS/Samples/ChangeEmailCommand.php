<?php

namespace Lidskasila\Glow;

class ChangeEmailCommand implements \Lidskasila\Glow\Commanding\Command
{

	public $email;

	public function __construct($email)
	{
		$this->email = $email;
	}
}
