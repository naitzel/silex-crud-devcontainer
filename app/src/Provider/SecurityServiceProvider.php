<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Silex\Provider\SecurityServiceProvider as BaseSecurityServiceProvider;

/**
 * Class SecurityServiceProvider.
 *
 * http://silex.sensiolabs.org/doc/providers/security.html
 */
class SecurityServiceProvider extends BaseSecurityServiceProvider
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        $app['security_path'] = preg_replace(array('/^\//', '/\/$/'), '', $app['security_path']);

        $app['security.firewalls'] = array(
            'login' => array(
                'pattern' => sprintf('^/%s/login$', $app['security_path']),
            ),
            'secured' => array(
                'pattern' => sprintf('^/%s', $app['security_path']),
                'form' => array(
                    'login_path' => sprintf('/%s/login', $app['security_path']),
                    'check_path' => sprintf('/%s/login_check', $app['security_path']),
                ),
                'logout' => array(
                    'logout_path' => sprintf('/%s/logout', $app['security_path']),
                ),
                'users' => $app->share(function () use ($app) {
                    return new UserServiceProvider($app);
                }),
            ),
            /**
             * Veja mais em: http://stackoverflow.com/questions/21909574/accessing-app-user-in-unsecured-area-silex?answertab=votes#tab-top
             */
            'web_secured' => array(
                'pattern' => '^/(?!login$)',
                'form' => array(
                    'login_path' => '/login',
                    'check_path' => '/login_check',
                ),
                'logout' => array(
                    'logout_path' => '/logout',
                ),
                'anonymous' => true,
                'users' => $app->share(function () use ($app) {
                    return new UserServiceProvider($app);
                }),
            ),
        );
        // inicialize
        parent::register($app);
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
        $access_rules = array();

        try {
            $access_rules_database = $app['db']->fetchAll('SELECT `path`, `roles`, `methods`, `host`, `ip` FROM `roles_access` ORDER BY `order` DESC');

            foreach ($access_rules_database as $rule) {
                $access_rules[] = array(
                    $rule['path'],
                    json_decode($rule['roles']),
                    $rule['methods'],
                    $rule['host'],
                    $rule['ip'],
                );
            }
        } catch (\PDOException $e) {
        }

        // Permissões de rotas
        $app['security.access_rules'] = $access_rules;

        parent::boot($app);
    }
}
