<?php

namespace LiteCQRS;

class ChangeEmailCommand implements \LiteCQRS\Commanding\Command
{

	public $email;

	public function __construct($email)
	{
		$this->email = $email;
	}
}
