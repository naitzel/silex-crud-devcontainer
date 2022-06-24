<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Service;

use Silex\ControllerResolver as BaseControllerResolver;
use Naitzel\SilexCrud\Controller\ContainerAware;

/**
 * Class ControllerResolver.
 *
 * http://silex.sensiolabs.org/doc/providers.html#controller-providers
 */
class ControllerResolver extends BaseControllerResolver
{
    /**
     * @see Silex\ControllerResolver::createController
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        $class = preg_replace('/Controller$/', '', $class);
        if (!class_exists($class) && !class_exists($class = sprintf('%s\%sController', str_replace('\Service', '\Controller', __NAMESPACE__), $class))) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $controller = new $class();

        if ($controller instanceof ContainerAware) {
            $controller->setContainer($this->app);
        }

        $methods = get_class_methods($controller);
        if (!in_array($method, $methods) && !in_array($method = sprintf('%sAction', $method), $methods)) {
            throw new \InvalidArgumentException(sprintf('Method "%s" does not exist.', $method));
        }

        return [$controller, $method];
    }
}
