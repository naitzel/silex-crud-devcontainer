<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider as BaseDoctrineServiceProvider;

/**
 * Class DoctrineServiceProvider.
 *
 * http://silex.sensiolabs.org/doc/providers/doctrine.html
 */
class DoctrineServiceProvider extends BaseDoctrineServiceProvider
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['dbs.options'] = array(
            'db' => array(
                'driver' => 'pdo_mysql',
                'dbname' => getenv('MYSQL_DATABASE') ?: 'develop',
                'host' => getenv('MYSQL_HOST') ?: 'mysql',
                'user' => getenv('MYSQL_USER') ?: 'develop',
                'password' => getenv('MYSQL_PASSWORD') ?: 'develop',
                'charset' => 'utf8',
            ),
        );

        //
        parent::register($app);
    }
}
