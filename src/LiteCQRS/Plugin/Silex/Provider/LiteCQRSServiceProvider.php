<?php

namespace LiteCQRS\Plugin\Silex\Provider;

use LiteCQRS\Plugin\Silex\ApplicationEventBus;
use LiteCQRS\Plugin\Silex\ApplicationCommandBus;
use LiteCQRS\Bus\IdentityMap\EventProviderQueue;
use LiteCQRS\Bus\IdentityMap\SimpleIdentityMap;
use LiteCQRS\Bus\EventMessageHandlerFactory;
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
            return new ApplicationCommandBus($app, $app['lite_cqrs.command_proxy_factories']);
        });

        $app['event_message_bus'] = $app->share(function (Application $app) {
            return new ApplicationEventBus($app, $app['lite_cqrs.event_proxy_factories']);
        });

        $app['lite_cqrs.event_queue'] = $app->share(function (Application $app) {
            return new EventProviderQueue($app['lite_cqrs.identity_map']);
        });

        $app['lite_cqrs.identity_map'] = $app->share(function () {
            return new SimpleIdentityMap();
        });

        $app['lite_cqrs.event_message_handler'] = $app->share(function (Application $app) {
            return new EventMessageHandlerFactory($app['event_message_bus'], $app['lite_cqrs.event_queue']);
        });

        $app['lite_cqrs.command_proxy_factories'] = $app->share(function (Application $app) {
            return array($app['lite_cqrs.event_message_handler']);
        });

        // Keep backwards compatibility
        $app['lite_cqrs.event_message_bus'] = $app->raw('event_message_bus');
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
