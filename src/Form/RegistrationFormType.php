<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

use function PHPSTORM_META\type;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '50'
                ],
                'label' => 'Nom de famille',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1'],
            ])
            ->add('firstName', TextType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '50'
                ],
                'label' => 'Prénom',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1']
            ])
            ->add('pseudo', TextType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '50'
                ],
                'label' => 'Pseudo',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1']
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '180'
                ],
                'label' => 'E-mail',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1']
            ])
            ->add('telephone', TextType::class, [
                'attr' => [
                    'minlength' => '10',
                    'maxlength' => '25'
                ],
                'label' => 'Numéro de téléphone',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-12 my-1']
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '255'
                ],
                'label' => 'Address postal',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-12 my-1']
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '25'
                ],
                'label' => 'Ville',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1']
            ])
            ->add('zipCode', TextType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '10'
                ],
                'label' => 'Code postal',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-6 my-1']
            ])
            //->add('picture')

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
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}