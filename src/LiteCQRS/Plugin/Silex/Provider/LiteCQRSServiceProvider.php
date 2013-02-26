<?php

namespace LiteCQRS\Plugin\Silex\Provider;

use LiteCQRS\Plugin\Silex;
use LiteCQRS\Bus;
use Silex\Application;

/**
 * @package LiteCQRS
 */
class LiteCQRSServiceProvider implements \Silex\ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['lite_cqrs.event_proxy_factories']   = array();
        $app['lite_cqrs.command_proxy_factories'] = array();
        $app['lite_cqrs.commands']                = array();
        $app['lite_cqrs.event_handlers']          = array();

        $app['command_bus'] = $app->share(function (Application $app) {
            return new Silex\ApplicationCommandBus($app, $app['lite_cqrs.command_proxy_factories']);
        });

        $app['lite_cqrs.identity_map'] = $app->share(function () {
            return new Bus\IdentityMap\SimpleIdentityMap();
        });

        $app['lite_cqrs.event_message_bus'] = $app->share(function (Application $app) {
            return new Silex\ApplicationEventBus($app, $app['lite_cqrs.event_proxy_factories']);
        });

        $app['lite_cqrs.event_message_handler'] = $app->share(function (Application $app) {
            return new Bus\EventMessageHandlerFactory($app['lite_cqrs.event_message_bus'], $app['lite_cqrs.identity_map']);
        });

        $app['lite_cqrs.command_proxy_factories'] = $app->share(function (Application $app) {
            return array($app['lite_cqrs.event_message_handler']);
        });
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $app['command_bus']->registerServices($app['lite_cqrs.commands']);

        $app['lite_cqrs.event_message_bus']->registerServices($app['lite_cqrs.event_handlers']);
    }
}
