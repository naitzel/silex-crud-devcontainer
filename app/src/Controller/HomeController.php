<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */

namespace Naitzel\SilexCrud\Controller;

use Naitzel\SilexCrud\Controller\ContainerAware;

/**
 * Class HomeController
 */
class HomeController extends ContainerAware
{
    public function indexAction()
    {
        return $this->render('index.twig');
    }
}
