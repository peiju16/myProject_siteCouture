<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', ChoiceType::class, [
            'label' => 'Civilité',
            'label_attr' => [
                'class' => 'text-dark p-1'
            ],
            'row_attr' => [
                'class' => 'col-lg-6 my-1',
            ],
            'placeholder' => 'Veuillez choisir votre civilité',
            'choices' => [
                    'Madame' => 'Mme',           // Label => Value
                    'Monsieur' => 'M',
                    'Mademoiselle' => 'Mlle',
                ],
                'expanded' => false,  // Displays options as radio buttons
                'multiple' => false, // Single selection only
                'required' => false, // Optional field
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

        ->add('imageFile', VichImageType::class, [
            'label' => 'Image',
            'label_attr' => [
                'class' => 'text-light'
            ],
            'required' => false,
            'allow_delete' => true,
            'delete_label' => 'Supprimer ce image',
            'download_label' => 'Télécharger ce image',
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/png',
                        'image/jpeg',
                        'image/svg',
                    ],
                    'mimeTypesMessage' => 'Veuillez choisir un image en format png/jpeg/svg',
                ])
            ],
            'row_attr' => ['class' => 'col-lg-12 my-1'],
        ])

        ->add('pleinPassword', PasswordType::class, [
                'label' => 'Entrer votre Mot de passe pour confirmer la modification',
                'label_attr' => [
                    'class' => 'text-dark p-1'
                ],
                'row_attr' => ['class' => 'col-lg-12 my-1'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre mot de passe',
                ]),
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
