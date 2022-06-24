<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

use Symfony\Component\Console\Application;
use Naitzel\SilexCrud\Command as Commands;
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\DBAL\Tools\Console\ConsoleRunner;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;

$console = new Application('Crud Admin', $app['composer']['version']);

$app->boot();

$console->add(new Commands\GeneratorCommand($app));
$console->add(new Commands\UserCreateCommand($app));
$console->add(new Commands\UserTableCommand($app));
$console->add(new Commands\ServerCommand($app));
$console->add(new Commands\RouterListCommand($app));
$console->add(new Commands\MakeControllerCommand($app));

/*
 * Doctrine CLI
 *
 * https://github.com/dflydev/dflydev-doctrine-orm-service-provider/issues/11
 */
$helperSet = new HelperSet(array(
    // DBAL Commands
    'db' => new ConnectionHelper($app['db']),
));

$console->setHelperSet($helperSet);
ConsoleRunner::addCommands($console);

return $console;
