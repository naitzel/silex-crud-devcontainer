<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Service;

use Silex\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InstitutionalTypeConverter
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function convert($id)
    {
        $type = $this->app['db']->fetchAssoc('SELECT * FROM `institutional_type` WHERE `id` = ?', array($id));

        if (!is_array($type)) {
            throw new NotFoundHttpException(sprintf('Institutional type %d does not exist', $id));
        }

        return $type;
    }
}
