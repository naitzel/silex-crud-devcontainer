<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();

/**
 * Helpers.
 */
require_once __DIR__.'/helpers.php';

/*
 * Configurações
 */

// Url painel
$app['security_path'] = '/painel';

// Prefixo url
$app['asset_path'] = '/';

// Habilitar modo desenvolvedor
$app['debug'] = getenv('DEVELOP_MODE') ?: false;

// Composer configurações
$app['composer'] = json_decode(file_get_contents(base_path('composer.json')), true);

// Habilitar Http Method Override
Request::enableHttpMethodParameterOverride();

// http://silex.sensiolabs.org/doc/providers/session.html
$app->register(new Naitzel\SilexCrud\Provider\SessionServiceProvider());

// http://silex.sensiolabs.org/doc/providers/form.html
$app->register(new Naitzel\SilexCrud\Provider\FormServiceProvider());

// http://silex.sensiolabs.org/doc/providers/translation.html
$app->register(new Naitzel\SilexCrud\Provider\TranslationServiceProvider());

// http://silex.sensiolabs.org/doc/providers/validator.html
$app->register(new Naitzel\SilexCrud\Provider\ValidatorServiceProvider());

// http://silex.sensiolabs.org/doc/providers/url_generator.html
$app->register(new Naitzel\SilexCrud\Provider\UrlGeneratorServiceProvider());

// http://silex.sensiolabs.org/doc/providers/doctrine.html
$app->register(new Naitzel\SilexCrud\Provider\DoctrineServiceProvider());

// http://silex.sensiolabs.org/doc/providers/swiftmailer.html
$app->register(new Naitzel\SilexCrud\Provider\SwiftmailerServiceProvider());

// http://silex.sensiolabs.org/doc/providers/service_controller.html
$app->register(new Naitzel\SilexCrud\Provider\ServiceControllerServiceProvider());

// http://silex.sensiolabs.org/doc/providers/security.html#traits
$app->register(new Naitzel\SilexCrud\Provider\RouteProvider());

// ExceptionServiceProvider
$app->register(new Naitzel\SilexCrud\Provider\ExceptionServiceProvider());

// http://silex.sensiolabs.org/doc/providers/twig.html
$app->register(new Naitzel\SilexCrud\Provider\TwigServiceProvider());

// http://silex.sensiolabs.org/doc/providers/http_fragment.html
$app->register(new Silex\Provider\HttpFragmentServiceProvider());

// http://glide.thephpleague.com/
$app->register(new Naitzel\SilexCrud\Provider\GlideProvider());

// S.E.O. Provider
$app->register(new Naitzel\SilexCrud\Provider\SeoProvider());

// https://github.com/cocur/slugify
$app->register(new Cocur\Slugify\Bridge\Silex\SlugifyServiceProvider());

// http://silex.sensiolabs.org/doc/providers/monolog.html
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => log_path(sprintf('%s.log', (new \DateTime())->format('Y-m-d'))),
));

if ($app['debug']) {
    // https://github.com/silexphp/Silex-WebProfiler
    $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
        'profiler.cache_dir' => cache_path(),
    ));
}

$app->register(new Naitzel\SilexCrud\Provider\TwigExtensionProvider());

// Serviço Institucional
$app->register(new Naitzel\SilexCrud\Provider\InstitutionalProvider());
$app->register(new Naitzel\SilexCrud\Provider\InstitutionalTypeProvider());

// Serviço Banner
$app->register(new Naitzel\SilexCrud\Provider\BannerProvider());
$app->register(new Naitzel\SilexCrud\Provider\BannerTypeProvider());

if (!defined('CONSOLE')) {
    // http://silex.sensiolabs.org/doc/providers/security.html
    $app->register(new Naitzel\SilexCrud\Provider\SecurityServiceProvider());
}


return $app;
