<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Naitzel\SilexCrud\Form\Type\StatusType;

class SeoForm extends AbstractType
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting from the
     * top most type. Type extensions can further modify the form.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', 'text', array(
                'required' => true,
                'label' => 'Url',
                'attr' => array(
                    'placeholder' => '^/pagina/teste$',
                ),
            ))
            ->add('title', 'text', array(
                'required' => false,
                'label' => 'Titulo'
            ))
            ->add('description', 'text', array(
                'required' => false,
                'label' => 'Descrição'
            ))
            ->add('keyword', 'text', array(
                'required' => false,
                'label' => 'Keyword'
            ))
            ->add('h1', 'text', array(
                'required' => false,
                'label' => 'H1'
            ))
            ->add('enabled', new StatusType())
        ;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        //
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'access';
    }
}
