<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Naitzel\SilexCrud\Twig\AssetTwigFunction;
use Naitzel\SilexCrud\Twig\CamelizeTwigFunction;
use Naitzel\SilexCrud\Twig\SecurityTwigFunction;
use Naitzel\SilexCrud\Twig\SeoTwigFunction;
use Cocur\Slugify\Bridge\Twig\SlugifyExtension;
use Cocur\Slugify\Slugify;

/**
 * Class TwigExtensionProvider.
 *
 * http://silex.sensiolabs.org/doc/providers/twig.html
 */
class TwigExtensionProvider implements ServiceProviderInterface
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
        // http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
        $app['twig']->addExtension(new AssetTwigFunction($app));

        // https://pt.wikipedia.org/wiki/CamelCase
        $app['twig']->addExtension(new CamelizeTwigFunction($app));

        // Security
        $app['twig']->addExtension(new SecurityTwigFunction($app));

        // S.E.O.
        $app['twig']->addExtension(new SeoTwigFunction($app));

        // https://github.com/cocur/slugify
        $app['twig']->addExtension(new SlugifyExtension(Slugify::create()));
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
        // TODO: Implement boot() method.
    }
}


