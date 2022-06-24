<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Controller\Security;

use Naitzel\SilexCrud\Controller\ContainerAware;
use Naitzel\SilexCrud\Form\InstitutionalForm;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class InstitutionalController extends ContainerAware
{
    /**
     * @return \Naitzel\SilexCrud\Service\InstitutionalService
     */
    private function getService()
    {
        return $this->get('service.institutional');
    }

    /**
     * Lista.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(array $institutional_type)
    {
        $institutional = $this->getService()->findAll($institutional_type['id']);

        return $this->render('list.twig', array(
            'data' => $institutional,
            'institutional_type' => $institutional_type,
        ));
    }

    /**
     * Adicionar.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request, array $institutional_type)
    {
        $institutional = array(
            'institutional_type' => $institutional_type,
            'type' => $institutional_type['id'],
        );

        $form = $this->createForm(new InstitutionalForm(), $institutional, array(
            'action' => $this->get('url_generator')->generate('s_institutional_create', array('institutional_type' => $institutional_type['id'])),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $data['url'] = $this->get('slugify')->slugify($data['url']);

                try {
                    $insert_query = 'INSERT INTO `institutional` (`title`, `url`, `body`, `enabled`, `type`, `created_at`, `updated_at`, `order`) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), 999)';
                    $this->db()->executeUpdate($insert_query, array($data['title'], $data['url'], $data['body'], $data['enabled'], $data['type']));

                    $this->flashMessage()->add('success', array('message' => 'Adicionado com sucesso.'));

                    return $this->redirect('s_institutional', array('institutional_type' => $institutional_type['id']));
                } catch (UniqueConstraintViolationException $e) {
                    $this->flashMessage()->add('danger', array('message' => 'Url já está em uso.'));
                }
            }
        }

        return $this->render('create.twig', array(
            'form' => $form->createView(),
            'institutional_type' => $institutional_type,
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
    public function editAction(Request $request, array $institutional_type, $id)
    {
        $institutional = $this->getService()->findById($id);

        if ($institutional === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais a pagina não foi encontrada.'));

            return $this->redirect('s_institutional', array('institutional_type' => $institutional_type['id']));
        }

        $form = $this->createForm(new InstitutionalForm(), $institutional, array(
            'action' => $this->get('url_generator')->generate('s_institutional_edit', array('institutional_type' => $institutional_type['id'], 'id' => $id)),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                $data['url'] = $this->get('slugify')->slugify($data['url']);
                try {
                    $update_query = 'UPDATE `institutional` SET `title` = ?, `url` = ?, `body` = ?, `enabled` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1';
                    $this->get('db')->executeUpdate($update_query, array($data['title'], $data['url'], $data['body'], $data['enabled'], $data['id']));

                    $this->flashMessage()->add('success', array('message' => 'Editado com sucesso.'));

                    return $this->redirect('s_institutional', array('institutional_type' => $institutional_type['id']));
                } catch (UniqueConstraintViolationException $e) {
                    $this->flashMessage()->add('danger', array('message' => 'Url já está em uso.'));
                }
            }
        }

        return $this->render('edit.twig', array(
            'form' => $form->createView(),
            'id' => $id,
            'institutional_type' => $institutional_type,
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
    public function deleteAction(Request $request, array $institutional_type)
    {
        $id = $request->request->get('id');
        $row_sql = $this->getService()->findById($id);

        if ($row_sql === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais não foi encontrado.'));
        } else {
            $this->get('db')->executeUpdate('UPDATE `institutional` SET `deleted_at` = NOW() WHERE `id` = ?', array($id));

            $this->flashMessage()->add('success', array('message' => 'Deletado com sucesso.'));
        }

        return $this->redirect('s_institutional', array('institutional_type' => $institutional_type['id']));
    }
}
