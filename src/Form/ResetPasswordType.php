<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('pleinPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => [
                'label' => 'Mot de passe',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                ]),
            ],
            ],
            'second_options' => [
                'label' => 'Confirmation votre mot de passe',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer votre mot de passe',
                ]),
            ],
            ],
            'invalid_message' => 'Votre mot de passe ne correspondent pas'
        ])

        ->add('newPassword', PasswordType::class, [
            'label' => 'Nouveau Mot de passe',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un nouveau mot de passe',
                ]),
            ],
        ])

        ->add('submit', SubmitType::class,[
            'label' => 'Submit',
            'row_attr' => ['class' => 'col-lg-12 mt-4'],
            'attr' => [
                'class' => 'main-button-icon'
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
