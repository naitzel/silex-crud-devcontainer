<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Controller\Security;

use Naitzel\SilexCrud\Controller\ContainerAware;
use Naitzel\SilexCrud\Form\SeoForm;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class SeoController extends ContainerAware
{
    /**
     * Lista.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $seo = $this->db()->fetchAll('SELECT * FROM `seo` ORDER BY `order` ASC');

        return $this->render('list.twig', array(
            'data' => $seo,
        ));
    }

    private function checkUrl(array $data)
    {
        if (substr($data['url'], 0, 1) !== '^') {
            $data['url'] = '^'.$data['url'];
        }

        if (substr($data['url'], -1) !== '$') {
            $data['url'] .= '$';
        }
        return $data;
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
        $seo = array();
        $form = $this->createForm(new SeoForm(), $seo, array(
            'action' => $this->get('url_generator')->generate('s_seo_create'),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $this->checkUrl($form->getData());

                try {

                    $update_query = 'INSERT INTO `seo` (`url`, `title`, `description`, `keyword`, `h1`, `enabled`, `created_at`, `updated_at`, `order`) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), 99)';
                    $this->db()->executeUpdate($update_query, array($data['url'], $data['title'], $data['description'], $data['keyword'], $data['h1'], $data['enabled']));

                    $this->flashMessage()->add('success', array('message' => 'Adicionado com sucesso.'));

                    return $this->redirect('s_seo');
                } catch (UniqueConstraintViolationException $e) {
                    if (strpos($e->getMessage(), "for key 'seo_url_unique'")) {
                        $this->get('session')->getFlashBag()->add('danger', array('message' => 'Url já está cadastrada.'));
                    } else {
                        $this->get('session')->getFlashBag()->add('danger', array('message' => $e->getMessage()));
                    }
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
        $seo = $this->get('db')->fetchAssoc('SELECT * FROM `seo` WHERE `id` = ? LIMIT 1;', array($id));

        if ($seo === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais a pagina não foi encontrada.'));

            return $this->redirect('s_seo');
        }

        $form = $this->createForm(new SeoForm(), $seo, array(
            'action' => $this->get('url_generator')->generate('s_seo_edit', array('id' => $id)),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $this->checkUrl($form->getData());

                try {
                    $update_query = 'UPDATE `seo` SET `url` = ?, `title` = ?, `description` = ?, `keyword` = ?, `h1` = ?, `enabled` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1';
                    $this->get('db')->executeUpdate($update_query, array($data['url'], $data['title'], $data['description'], $data['keyword'], $data['h1'], $data['enabled'], $data['id']));

                    $this->flashMessage()->add('success', array('message' => 'Editado com sucesso.'));

                    return $this->redirect('s_seo');

                } catch (UniqueConstraintViolationException $e) {
                    if (strpos($e->getMessage(), "for key 'seo_url_unique'")) {
                        $this->get('session')->getFlashBag()->add('danger', array('message' => 'Url já está cadastrada.'));
                    } else {
                        $this->get('session')->getFlashBag()->add('danger', array('message' => $e->getMessage()));
                    }
                }
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
        $row_sql = $this->get('db')->fetchAssoc('SELECT * FROM `seo` WHERE `id` = ? LIMIT 1;', array($id));

        if ($row_sql === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais não foi encontrado.'));
        } else {
            $this->get('db')->executeUpdate('DELETE FROM `seo` WHERE `id` = ?', array($id));

            $this->flashMessage()->add('success', array('message' => 'Deletado com sucesso.'));
        }

        return $this->redirect('s_seo');
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

        $orders = $request->request->get('order');

        foreach ($orders as $order => $item) {
            $this->get('db')->executeUpdate('UPDATE `seo` SET `order` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1', array($order, $item));
        }

        return $this->json($orders, 201);
    }
}
