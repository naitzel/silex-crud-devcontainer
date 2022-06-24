<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\Provider\TwigServiceProvider as BaseTwigServiceProvider;

/**
 * Class TwigServiceProvider.
 *
 * http://silex.sensiolabs.org/doc/providers/twig.html
 */
class TwigServiceProvider extends BaseTwigServiceProvider
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        parent::register($app);
        $app['twig.path'] = array(view_path());
        $app['twig.options'] = array(
            /*
             * Um caminho absoluto onde armazenar os modelos compilados, ou
             * falso para desabilitar o cache (padrão é false).
             */
            'cache' => $app['debug'] ? false : cache_path(),
            /*
             * Se definido como false, Twig irá silenciosamente ignorar
             * variáveis inválidas (variáveis e ou atributos / métodos que não
             * existem) e substituí-los por um valor nulo. Quando definido como
             * verdadeiro, Twig lança uma exceção (padrão é false).
             */
            'strict_variables' => $app['debug'],
        );
    }
}
