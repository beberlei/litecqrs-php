<?php

namespace LidskaSila\Glow;


class User extends AggregateRoot
{

	private $email;

	public function __construct(UserId $id)
	{
		$this->setId($id);
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function changeEmail($email)
	{
		$this->apply(new EmailChangedEvent([ 'email' => $email ]));
	}

	public function changeInvalidEventName()
	{
		$this->apply(new InvalidEvent());
	}

	protected function applyEmailChanged($event)
	{
		$this->email = $event->email;
	}
}
