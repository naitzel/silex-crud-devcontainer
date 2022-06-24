<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Controller\Security;

use Naitzel\SilexCrud\Controller\ContainerAware;
use Naitzel\SilexCrud\Form\AccessForm;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class AccessController extends ContainerAware
{
    /**
     * Lista.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $access = $this->db()->fetchAll('SELECT * FROM `roles_access` ORDER BY `order` DESC');

        foreach ($access as $key => $ac) {
            $access[$key] = $this->decode($ac);
        }

        return $this->render('list.twig', array(
            'data' => $access,
        ));
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
        $access = array(
            'role_choices' => $this->getChoiceRoles(),
        );
        $form = $this->createForm(new AccessForm(), $access, array(
            'action' => $this->get('url_generator')->generate('s_access_create'),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $this->encode($form->getData());

                try {
                    $data['order'] = (int) $this->db()->fetchAssoc('SELECT COUNT(1) as `total` FROM `roles_access`')['total'];

                    $insert_query = 'INSERT INTO `roles_access` (`path`, `roles`, `methods`, `host`, `ip`, `order`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())';
                    $this->db()->executeUpdate($insert_query, array($data['path'], $data['roles'], $data['methods'], $data['host'], $data['ip'], $data['order']));

                    $this->flashMessage()->add('success', array('message' => 'Adicionado com sucesso.'));

                    return $this->redirect('s_access');
                } catch (UniqueConstraintViolationException $e) {
                    $this->flashMessage()->add('danger', array('message' => 'Regra já existe cadastrada, insira um outra regra.'));
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
        $access = $this->get('db')->fetchAssoc('SELECT * FROM `roles_access` WHERE `id` = ? LIMIT 1;', array($id));

        if ($access === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais a pagina não foi encontrada.'));

            return $this->redirect('s_access');
        }

        $access = $this->decode($access);
        $access['role_choices'] = $this->getChoiceRoles();

        $form = $this->createForm(new AccessForm(), $access, array(
            'action' => $this->get('url_generator')->generate('s_access_edit', array('id' => $id)),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $this->encode($form->getData());

                try {
                    $update_query = 'UPDATE `roles_access` SET `path` = ?, `roles` = ?, `methods` = ?, `host` = ?, `ip` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1';
                    $this->get('db')->executeUpdate($update_query, array($data['path'], $data['roles'], $data['methods'], $data['host'], $data['ip'], $data['id']));
                } catch (UniqueConstraintViolationException $e) {
                    $this->flashMessage()->add('danger', array('message' => 'Regra já existe cadastrada, insira um outra regra.'));
                }

                $this->flashMessage()->add('success', array('message' => 'Editado com sucesso.'));

                return $this->redirect('s_access');
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
        $row_sql = $this->get('db')->fetchAssoc('SELECT * FROM `roles_access` WHERE `id` = ? LIMIT 1;', array($id));

        if ($row_sql === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais não foi encontrado.'));
        } else {
            $this->get('db')->executeUpdate('DELETE FROM `roles_access` WHERE `id` = ?', array($id));

            $this->flashMessage()->add('success', array('message' => 'Deletado com sucesso.'));
        }

        return $this->redirect('s_access');
    }

    /**
     * Order.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function orderAction(Request $request)
    {
        if (!$request->request->has('order')) {
            return $this->json(array(), 500);
        }

        $orders = array_reverse($request->request->get('order'));

        foreach ($orders as $order => $item) {
            $this->get('db')->executeUpdate('UPDATE `roles_access` SET `order` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1', array($order, $item));
        }

        return $this->json($orders, 201);
    }

    /**
     * @return array
     */
    private function getChoiceRoles()
    {
        $roles = array();

        $roles_fetch = $this->db()->fetchAll('SELECT `role`, `description` FROM `roles`');

        foreach ($roles_fetch as $role) {
            $roles[$role['role']] = $role['description'];
        }

        return $roles;
    }

    /**
     * @param  array
     *
     * @return array
     */
    private function encode(array $data)
    {
        if (is_array($data['roles']) && count($data['roles']) > 0) {
            $data['roles'] = json_encode($data['roles']);
        } else {
            $data['roles'] = null;
        }

        if (is_array($data['methods']) && count($data['methods']) > 0) {
            $data['methods'] = json_encode($data['methods']);
        } else {
            $data['methods'] = null;
        }

        return $data;
    }

    /**
     * @param  array
     *
     * @return array
     */
    private function decode(array $data)
    {
        if (is_array($roles = json_decode($data['roles']))) {
            $data['roles'] = $roles;
        } else {
            $data['roles'] = array();
        }

        if (is_array($methods = json_decode($data['methods']))) {
            $data['methods'] = $methods;
        } else {
            $data['methods'] = array();
        }

        return $data;
    }
}
