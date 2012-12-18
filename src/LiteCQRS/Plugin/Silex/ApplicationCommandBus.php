<?php

namespace LiteCQRS\Plugin\Silex;

use Silex\Application;

/**
 * @package LiteCQRS
 */
class ApplicationCommandBus extends \LiteCQRS\Bus\SequentialCommandBus
{
    protected $application;
    protected $commandServices = array();

    /**
     * @param Application $application
     * @param array       $proxyFactories
     */
    public function __construct(Application $application, array $proxyFactories = array())
    {
        parent::__construct($proxyFactories);

        $this->application = $application;
    }

    /**
     * @param array $commandServices
     */
    public function registerServices(array $commandServices)
    {
        $this->commandServices = $commandServices;
    }

    /**
     * @param  string $commandType
     * @return object
     */
    public function getService($commandType)
    {
        if (false == isset($this->commandServices[$commandType])) {
            throw new \RuntimeException("No command handler exists for command '" . $commandType . "'");
        }

        $serviceId = $this->commandServices[$commandType];

        if (false == isset($this->application[$serviceId])) {
            throw new \RuntimeException("Silex Application has no service '" . $serviceId . "' that is registered for command '" . $commandType . "'");
        }

        return $this->application[$serviceId];
    }
}
