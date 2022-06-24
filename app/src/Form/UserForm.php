<?php

/**
 *  (c) Rogério Adriano da Silva <rogerioadris.silva@gmail.com>.
 */
namespace Naitzel\SilexCrud\Form;

use Naitzel\SilexCrud\Form\Type\StatusType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserForm extends AbstractType
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
        $required = true;
        if (array_key_exists('data', $options) && array_key_exists('id', $options['data'])) {
            $required = false;
        }

        $builder

            // username
            ->add('username', 'text', array(
                'required' => true,
                'label' => 'Nome de usuário',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'min' => 8,
                        'max' => 50,
                    )),
                ),
            ))

            // email
            ->add('email', 'text', array(
                'required' => true,
                'label' => 'E-mail',
                'constraints' => array(
                    new Assert\Email(),
                ),
            ))

            // password
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Os campos de senha devem corresponder.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => $required,
                'first_options' => array('label' => 'Senha'),
                'second_options' => array('label' => 'Confirmação senha'),
                'constraints' => array(
                ),
            ))

            // name
            ->add('name', 'text', array(
                'required' => true,
                'label' => 'Nome',
                'constraints' => array(
                ),
            ))

            // roles
            ->add('roles', 'choice', array(
                'label' => 'Regras',
                'multiple' => true,
                'choices' => array_key_exists('role_choices', $options['data']) ? $options['data']['role_choices'] : array(),
                'required' => false,
                'constraints' => array(
                ),
            ))

            // enabled
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
        return 'user';
    }
}
