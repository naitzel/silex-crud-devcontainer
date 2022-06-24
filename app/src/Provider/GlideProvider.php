<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use League\Glide\ServerFactory;

class GlideProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        /* \outputImage */
        $app['glide'] = ServerFactory::create(array(
            'source' => web_path('upload'),
            'cache' => web_path('upload/.cache'),
        ));
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
