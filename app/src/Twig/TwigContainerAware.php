<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Twig;

use Silex\Application;
use Naitzel\SilexCrud\Traits\ContainerTrait;

/**
 * Class TwigContainerAware.
 *
 * http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension
 */
abstract class TwigContainerAware extends \Twig_Extension
{
    use ContainerTrait;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->setContainer($app);
    }
}
