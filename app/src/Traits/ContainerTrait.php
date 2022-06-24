<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Traits;

use Silex\Application;

trait ContainerTrait
{
    /**
     * @var Application
     */
    private $app;

    /**
     * Setar Application.
     *
     * @param Application $app
     */
    public function setContainer(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Retorna Application.
     *
     * @return Application
     */
    protected function getContainer()
    {
        return $this->app;
    }

    /**
     * Retorna serviço.
     *
     * @param $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return $this->getContainer()[$service];
    }
}
