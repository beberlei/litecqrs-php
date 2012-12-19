<?php

namespace LiteCQRS\Plugin\Silex;

use Silex\Application;

/**
 * @package LiteCQRS
 */
class ApplicationEventBus extends \LiteCQRS\Bus\AbstractEventMessageBus
{
    protected $application;
    protected $eventServices = array();

    /**
     * @param Application $application
     * @param array $proxyFactories
     */
    public function __construct(Application $application, array $proxyFactories)
    {
        $this->application = $application;

        parent::__construct($proxyFactories);
    }

    /**
     * @param array $eventServices
     */
    public function registerServices(array $eventServices)
    {
        $this->eventServices = $eventServices;
    }

    /**
     * @param string $eventName
     * @return array
     */
    protected function getHandlers($eventName)
    {
        $handlers = array();
        $eventName = strtolower($eventName);

        if (!isset($this->eventServices[$eventName])) {
            return array();
        }


        foreach ($this->eventServices[$eventName] as $serviceIds) {
            $serviceIds = (array) $serviceIds;

            foreach ($serviceIds as $serviceId) {
                $handlers[] = $this->application[$serviceId];
            }
        }

        return $handlers;
    }
}
