<?php

/*
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>
 */
namespace Naitzel\SilexCrud\Controller;

use Naitzel\SilexCrud\Traits as Traits;
use Silex\Application\SecurityTrait;

/**
 * Class ContainerAware.
 */
abstract class ContainerAware
{
    use Traits\ContainerTrait,
        Traits\ResponseTrait,
        Traits\DoctrineTrait,
        Traits\FlashMessageTrait,
        SecurityTrait;

    /**
     * Renderiza pagina.
     *
     * @param       $name
     * @param array $parameters
     *
     * @return mixed
     */
    protected function render($name, array $parameters = array())
    {
        if (substr($name, 0, 1) !== '/') {
            $patterns = array(
                sprintf('/^(\\\\)?%s(\\\\)?/', str_replace('\\', '(\\\\)?', __NAMESPACE__)),
                '/Controller$/',
                '/\\\/',
            );

            $replaces = array(
                '',
                '',
                DIRECTORY_SEPARATOR,
            );

            $prefix = strtolower(preg_replace($patterns, $replaces, get_class($this)));
        } else {
            $prefix = '';
            $name = substr($name, 1);
        }

        return $this->get('twig')->render(sprintf('%s/%s', $prefix, $name), $parameters);
    }

    /**
     * Returns a form builder.
     *
     * @param string|FormTypeInterface $type    The type of the form
     * @param mixed                    $data    The initial data
     * @param array                    $options The options
     *
     * @return FormBuilderInterface The form builder
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException if any given option is not applicable to the given type
     */
    protected function createForm($type = 'form', $data = null, array $options = array())
    {
        return $this->get('form.factory')->createBuilder($type, $data, $options)->getForm();
    }
}
