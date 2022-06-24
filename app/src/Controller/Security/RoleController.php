<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Controller\Security;

use Naitzel\SilexCrud\Controller\ContainerAware;
use Naitzel\SilexCrud\Form\RoleForm;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class RoleController extends ContainerAware
{
    /**
     * Lista.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $roles = $this->db()->fetchAll('SELECT * FROM `roles`');

        return $this->render('list.twig', array(
            'data' => $roles,
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
        $role = array();
        $form = $this->createForm(new RoleForm(), $role, array(
            'action' => $this->get('url_generator')->generate('s_role_create'),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                try {
                    $insert_query = 'INSERT INTO `roles` (`role`, `is_removable`, `description`, `created_at`, `updated_at`) VALUES (?, true, ?, NOW(), NOW())';
                    $this->db()->executeUpdate($insert_query, array($data['role'], $data['description']));

                    $this->flashMessage()->add('success', array('message' => 'Adicionado com sucesso.'));

                    return $this->redirect('s_role');
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
        $role = $this->get('db')->fetchAssoc('SELECT * FROM `roles` WHERE `id` = ? LIMIT 1;', array($id));

        if ($role === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais a pagina não foi encontrada.'));

            return $this->redirect('s_role');
        }

        $form = $this->createForm(new RoleForm(), $role, array(
            'action' => $this->get('url_generator')->generate('s_role_edit', array('id' => $id)),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                try {
                    $update_query = 'UPDATE `roles` SET `role` = ?, `description` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1';
                    $this->get('db')->executeUpdate($update_query, array($data['role'], $data['description'], $data['id']));
                } catch (UniqueConstraintViolationException $e) {
                    $this->flashMessage()->add('danger', array('message' => 'Regra já existe cadastrada, insira um outra regra.'));
                }

                $this->flashMessage()->add('success', array('message' => 'Editado com sucesso.'));

                return $this->redirect('s_role');
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
        $id = (int) $request->request->get('id', 0);

        try {
            $this->get('db')->executeUpdate('DELETE FROM `roles` WHERE `id` = ? AND `is_removable` IS TRUE', array($id));

            $this->flashMessage()->add('success', array('message' => 'Deletado com sucesso.'));
        } catch (ModelNotFoundException $e) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais não foi encontrado a regra.'));
        }

        return $this->redirect('s_role');
    }
}
