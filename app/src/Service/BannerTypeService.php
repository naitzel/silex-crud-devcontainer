<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */

namespace Naitzel\SilexCrud\Service;

use Silex\Application;
use Naitzel\SilexCrud\Traits\DoctrineTrait;

class BannerTypeService
{
    use DoctrineTrait;

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    protected function db()
    {
        return $this->app['db'];
    }

    public function findAll()
    {
        return $this->fetchAll('SELECT * FROM `banner_type` WHERE `deleted_at` IS NULL');
    }

    public function findById(int $code)
    {
        return $this->fetchAssoc('SELECT * FROM `banner_type` WHERE `id` = ?', array($code));
    }

    public function findByUrl(string $url)
    {
        return $this->fetchAssoc('SELECT * FROM `banner_type` WHERE `url` = ?', array($url));
    }
}
