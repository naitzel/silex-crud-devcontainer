<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\Provider\SwiftmailerServiceProvider as BaseSwiftmailerServiceProvider;

/**
 * Class SwiftmailerServiceProvider.
 *
 * http://silex.sensiolabs.org/doc/providers/swiftmailer.html
 */
class SwiftmailerServiceProvider extends BaseSwiftmailerServiceProvider
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        //
        parent::register($app);

        $app['swiftmailer.use_spool'] = false;
        $app['swiftmailer.options'] = array(
            'host' => getenv('MAILER_HOST') ?: 'host',
            'port' => getenv('MAILER_PORT') ?: '25',
            'username' => getenv('MAILER_USERNAME') ?: 'username',
            'password' => getenv('MAILER_PASSWORD') ?: 'password',
            'from' => getenv('MAILER_FROM') ?: 'mail',
            'encryption' => getenv('MAILER_ENCRYPTION') ?: null,
            'auth_mode' => getenv('MAILER_AUTH_MODE') ?: null,
        );
    }
}
