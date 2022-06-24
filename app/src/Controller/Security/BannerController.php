<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Controller\Security;

use Naitzel\SilexCrud\Controller\ContainerAware;
use Naitzel\SilexCrud\Form\BannerForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;

class BannerController extends ContainerAware
{
    /**
     * @return \Naitzel\SilexCrud\Service\BannerService
     */
    private function getService()
    {
        return $this->get('service.banner');
    }

    /**
     * Lista.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(array $banner_type)
    {
        $banner = $this->getService()->findAll($banner_type['id']);

        return $this->render('list.twig', array(
            'data' => $banner,
            'banner_type' => $banner_type,
        ));
    }

    /**
     * Adicionar.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request, array $banner_type)
    {
        $banner = array(
            'banner_type' => $banner_type,
            'type' => $banner_type['id'],
        );

        $form = $this->createForm(new BannerForm(), $banner, array(
            'action' => $this->get('url_generator')->generate('s_banner_create', array('banner_type' => $banner_type['id'])),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['image'] instanceof UploadedFile) {
                    $image = $data['image'];
                    $fs = new Filesystem();
                    $directory = web_path('upload/banner');

                    $image_name = sha1(uniqid(mt_rand(), true)).'.'.$image->guessExtension();

                    if (!$fs->exists($directory)) {
                        $fs->mkdir($directory, 0777);
                    }

                    $image->move($directory, $image_name);
                    $fs->chmod($directory.'/'.$image_name, 0777);

                    $data['image'] = $image_name;
                }

                $insert_query = 'INSERT INTO `banner` (`type`, `title`, `url`, `image`, `enabled`, `show_in`, `show_out`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())';
                $this->db()->executeUpdate($insert_query, array($data['type'], $data['title'], $data['url'], $data['image'], $data['enabled'], $data['show_in'], $data['show_out']));

                $this->flashMessage()->add('success', array('message' => 'Adicionado com sucesso.'));

                return $this->redirect('s_banner', array('banner_type' => $banner_type['id']));
            }
        }

        return $this->render('create.twig', array(
            'form' => $form->createView(),
            'banner_type' => $banner_type,
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
    public function editAction(Request $request, array $banner_type, $id)
    {
        $banner = $this->getService()->findById($id);

        if ($banner === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais a pagina não foi encontrada.'));

            return $this->redirect('s_banner');
        }

        $image_name = $banner['image'];
        unset($banner['image']);

        $form = $this->createForm(new BannerForm(), $banner, array(
            'action' => $this->get('url_generator')->generate('s_banner_edit', array('banner_type' => $banner_type['id'], 'id' => $id)),
            'method' => 'POST',
        ));

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();

                if ($data['image'] instanceof UploadedFile) {
                    $image = $data['image'];
                    $fs = new Filesystem();
                    $directory = web_path('upload/banner');

                    $image_name = sha1(uniqid(mt_rand(), true)).'.'.$image->guessExtension();

                    if (!$fs->exists($directory)) {
                        $fs->mkdir($directory, 0777);
                    }

                    $image->move($directory, $image_name);
                    $fs->chmod($directory.'/'.$image_name, 0777);
                }

                $data['image'] = $image_name;

                $update_query = 'UPDATE `banner` SET `title` = ?, `url` = ?, `image` = ?, `enabled` = ?, `show_in` = ?, `show_out` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1';
                $this->get('db')->executeUpdate($update_query, array($data['title'], $data['url'], $data['image'], $data['enabled'], $data['show_in'], $data['show_out'], $data['id']));

                $this->flashMessage()->add('success', array('message' => 'Editado com sucesso.'));

                return $this->redirect('s_banner', array('banner_type' => $banner_type['id']));
            }
        }

        return $this->render('edit.twig', array(
            'form' => $form->createView(),
            'banner_type' => $banner_type,
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
    public function deleteAction(Request $request, array $banner_type)
    {
        $id = $request->request->get('id');
        $row_sql = $this->getService()->findById($id);

        if ($row_sql === false) {
            $this->flashMessage()->add('warning', array('message' => 'Desculpe, mais não foi encontrado.'));
        } else {
            $this->get('db')->executeUpdate('UPDATE `banner` SET `deleted_at` = NOW() WHERE `id` = ?', array($id));

            $this->flashMessage()->add('success', array('message' => 'Deletado com sucesso.'));
        }

        return $this->redirect('s_banner', array('banner_type' => $banner_type['id']));
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
            $this->get('db')->executeUpdate('UPDATE `banner` SET `order` = ?, `updated_at` = NOW() WHERE `id` = ? LIMIT 1', array($order, $item));
        }

        return $this->json($orders, 201);
    }
}
