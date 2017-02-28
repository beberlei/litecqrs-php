<?php

namespace LiteCQRS;

class ChangeEmailCommand implements Command
{

	public $email;

	public function __construct($email)
	{
		$this->email = $email;
	}
}
