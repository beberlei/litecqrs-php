<?php

namespace LidskaSila\Glow\Eventing;

use Exception;
use LidskaSila\Glow\DomainEvent;

class SynchronousInProcessEventBus implements EventMessageBus
{

	/**
	 * @var EventHandlerLocator
	 */
	private $locator;

	public function __construct(EventHandlerLocator $locator)
	{
		$this->locator = $locator;
	}

	public function publish(DomainEvent $event)
	{
		$eventName = new EventName($event);
		$services  = $this->locator->getHandlersFor($eventName);

		foreach ($services as $service) {
			$this->invokeEventHandler($service, $eventName, $event);
		}
	}

	protected function invokeEventHandler($service, $eventName, $event)
	{
		try {
			$methodName = 'on' . $eventName;

			$service->$methodName($event);
		} catch (Exception $e) {
			if ($event instanceof EventExecutionFailed) {
				return;
			}

			$this->publish(new EventExecutionFailed([
				'service'   => get_class($service),
				'exception' => $e,
				'event'     => $event,
			]));
		}
	}
}

