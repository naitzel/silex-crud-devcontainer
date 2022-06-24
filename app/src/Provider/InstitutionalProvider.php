<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Naitzel\SilexCrud\Service\InstitutionalService;

class InstitutionalProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        $app['service.institutional'] = $app->share(function (Application $app) {
            return new InstitutionalService($app);
        });
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
        // boot
    }
}
