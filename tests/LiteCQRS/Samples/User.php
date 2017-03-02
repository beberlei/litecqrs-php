<?php

namespace LiteCQRS;

use Ramsey\Uuid\UuidInterface;

class User extends AggregateRoot
{

	private $email;

	public function __construct(UuidInterface $uuid)
	{
		$this->setId($uuid);
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function changeEmail($email)
	{
		$this->apply(new ChangeEmailEvent([ 'email' => $email ]));
	}

	protected function applyChangeEmail($event)
	{
		$this->email = $event->email;
	}

	public function changeInvalidEventName()
	{
		$this->apply(new InvalidEvent([]));
	}
}
