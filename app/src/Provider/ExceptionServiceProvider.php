<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExceptionServiceProvider.
 *
 * http://silex.sensiolabs.org/doc/usage.html#error-handlers
 */
class ExceptionServiceProvider implements ServiceProviderInterface
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
        $app->error(
            function (\Exception $e, $code) use ($app) {
                if ($app['debug']) {
                    return; // exibir erro no ambiente desenvolvimento.
                }

                // Exibir pagina de erro personalizada.
                // busca pagina de erro pelo código 404.twig, 40x.twig, 4xx.twig ou error.twig
                $templates = array(
                    'errors/'.$code.'.twig',
                    'errors/'.substr($code, 0, 2).'x.twig',
                    'errors/'.substr($code, 0, 1).'xx.twig',
                    'errors/error.twig',
                );

                return new Response($app['twig']->resolveTemplate($templates)->render(array(
                    'code' => $code,
                    'error' => $e->getMessage(),
                )), $code);
            }
        );
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
