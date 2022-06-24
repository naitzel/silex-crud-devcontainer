<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Silex\ServiceControllerResolver;
use Naitzel\SilexCrud\Service\ControllerResolver;

/**
 * Class ServiceControllerServiceProvider.
 *
 * http://silex.sensiolabs.org/doc/providers/service_controller.html
 */
class SeoProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        // TODO: Implement register() method.
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        $app['get_seo'] = $app->share(function(Application $app) {
            return $app['db']->fetchAssoc('SELECT * FROM `seo` WHERE ? REGEXP `url` AND `enabled` = 1 ORDER BY `order` ASC', array($app['request']->getPathInfo()));
        });
    }
}
