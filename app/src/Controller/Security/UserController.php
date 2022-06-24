<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Controller\Security;

use Naitzel\SilexCrud\Controller\ContainerAware;
use Naitzel\SilexCrud\Form\UserForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class UserController extends ContainerAware
{
    /**
     * Lista.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $users = $this->db()->fetchAll('SELECT * FROM `users`');

        foreach ($users as $key => $user) {
            $users[$key]['roles'] = $this->db()->fetchAll('SELECT `r`.* FROM `roles` AS `r`, `users_roles` AS `ur` WHERE `r`.`id` = `ur`.`role` AND `ur`.`user` = ?;', array($user['id']));
        }

        return $this->render('list.twig', array(
            'data' => $users,
        ));
    }

    /**
     * @return array
     */
    private function getChoiceRoles()
    {
        $roles = array();

        $roles_fetch = $this->db()->fetchAll('SELECT `id`, `description` FROM `roles`');

        foreach ($roles_fetch as $role) {
            $roles[$role['id']] = $role['description'];
        }

        return $roles;
    }

    /**
     * @param  array
     *
     * @return string
     */
    private function encryptPassword(array $data)
    {
        // Encrypt password
        $user_model = new User($data['username'], $data['password']);
        $encoder = $this->get('security.encoder_factory')->getEncoder($user_model);

        return $encoder->encodePassword($user_model->getPassword(), $user_model->getSalt());
    }

    /**
     * Adicionar.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $user = array(
            'role_choices' => $this->getChoiceRoles(),
        );

        $form = $this->createForm(new UserForm(), $user, array(
            'action' => $this->get('url_generator')->generate('s_user_create'),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $data['password'] = $this->encryptPassword($data);

                try {
                    $insert_query = 'INSERT INTO `users` (`username`, `password`, `email`, `name`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, NOW(), NOW())';
                    $this->db()->executeUpdate($insert_query, array($data['username'], $data['password'], $data['email'], $data['name']));

                    $user['id'] = $this->get('db')->lastInsertId();

                    $data['roles'] = array_unique($data['roles']);
                    foreach ($data['roles'] as $role) {
                        $this->db()->executeUpdate('INSERT INTO `users_roles` (`user`, `role`) VALUES (?, ?);', array($user['id'], $role));
                    }

                    $this->flashMessage()->add('success', array('message' => 'Adicionado com sucesso.'));

                    return $this->redirect('s_user');
                } catch (UniqueConstraintViolationException $e) {
                    $this->flashMessage()->add('danger', array('message' => sprintf('Registro já existe cadastrado. "%s".', substr($e->getMessage(), strpos($e->getMessage(), 'SQLSTATE')))));
                }
            }
        }

        return $this->render('create.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Editar.
     *
     * @param Request $request
     * @param mixed   $id
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, $id)
    {
        $user = $this->get('db')->fetchAssoc('SELECT * FROM `users` WHERE `id` = ? LIMIT 1;', array($id));

        if ($user === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais a pagina não foi encontrada.'));

            return $this->redirect('s_user');
        }

        $user['role_choices'] = $this->getChoiceRoles();

        $roles_selected = $this->db()->fetchAssoc(sprintf('SELECT GROUP_CONCAT(`role`) as role FROM `users_roles` WHERE `user` = %u', $user['id']))['role'];

        if (null !== $roles_selected) {
            $user['roles'] = array_map(function ($val) { return (int) $val; }, explode(',', $roles_selected));
        }

        $form = $this->createForm(new UserForm(), $user, array(
            'action' => $this->get('url_generator')->generate('s_user_edit', array('id' => $id)),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                try {
                    if (is_null($data['password'])) {
                        $data['password'] = $user['password'];
                    } else {
                        $data['password'] = $this->encryptPassword($data);
                    }

                    $update_query = 'UPDATE `users` SET `username` = ?, `password` = ?, `email` = ?, `name` = ?, `enabled` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1;';
                    $this->get('db')->executeUpdate($update_query, array($data['username'], $data['password'], $data['email'], $data['name'], $data['enabled'], $data['id']));

                    // Deletar regras do usuário
                    $this->get('db')->executeUpdate('DELETE FROM `users_roles` WHERE  `user`= ?;', array($data['id']));

                    // Adicionar regras do usuário
                    $data['roles'] = array_unique($data['roles']);
                    foreach ($data['roles'] as $role) {
                        $this->db()->executeUpdate('INSERT INTO `users_roles` (`user`, `role`) VALUES (?, ?);', array($data['id'], $role));
                    }
                } catch (UniqueConstraintViolationException $e) {
                    $this->flashMessage()->add('danger', array('message' => 'Regra já existe cadastrada, insira um outra regra.'));
                }

                $this->flashMessage()->add('success', array('message' => 'Editado com sucesso.'));

                return $this->redirect('s_user');
            }
        }

        return $this->render('edit.twig', array(
            'form' => $form->createView(),
            'id' => $id,
        ));
    }

    /**
     * Deletar.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $id = $request->request->get('id');
        $row_sql = $this->get('db')->fetchAssoc('SELECT * FROM `users` WHERE `id` = ? LIMIT 1;', array($id));

        if ($row_sql === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais não foi encontrado.'));
        } else {
            $this->get('db')->executeUpdate('DELETE FROM `users_roles` WHERE `user` = ?', array($id));
            $this->get('db')->executeUpdate('DELETE FROM `users` WHERE `id` = ?', array($id));

            $this->flashMessage()->add('success', array('message' => 'Deletado com sucesso.'));
        }

        return $this->redirect('s_user');
    }
}
