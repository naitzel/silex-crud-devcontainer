<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */

namespace Naitzel\SilexCrud\Provider;

use Silex\Application;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class UserServiceProvider.
 *
 * http://silex.sensiolabs.org/doc/providers/security.html
 */
class UserServiceProvider implements UserProviderInterface
{
    /** @var Application */
    private $application;

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function loadUserByUsername($username)
    {
        $user = $this->application['db']->fetchAssoc('SELECT * FROM `users` WHERE `username` = ? AND `deleted_at` IS NULL', array(trim($username)));

        if (false === $user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $roles = explode(',', $this->application['db']->fetchAssoc('SELECT GROUP_CONCAT(`R`.`role`) as role FROM `users_roles` AS `UR`, `roles` AS `R` WHERE `UR`.`user` = ? AND `UR`.`role` = `R`.`id`', array($user['id']))['role']);

        return new User($user['username'], $user['password'], $roles, $user['enabled']);
    }

    /**
     * @param UserInterface $user
     *
     * @return string
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }
}
