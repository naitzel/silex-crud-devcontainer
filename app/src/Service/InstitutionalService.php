<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Service;

use Silex\Application;
use Naitzel\SilexCrud\Traits\DoctrineTrait;

class InstitutionalService
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

    public function findAll(int $type)
    {
        return $this->fetchAll('SELECT * FROM `institutional` WHERE `deleted_at` IS NULL AND `type` = ? ORDER BY `order` ASC', array($type));
    }

    public function findById(int $code)
    {
        return $this->fetchAssoc('SELECT * FROM `institutional` WHERE `id` = ? ORDER BY `order` ASC', array($code));
    }

    public function findByUrl(string $url)
    {
        return $this->fetchAssoc('SELECT * FROM `institutional` WHERE `url` = ? ORDER BY `order` ASC', array($url));
    }
}
