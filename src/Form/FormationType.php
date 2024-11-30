<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'attr' => [
                'minlength' => '2',
                'maxlength' => '255'
            ],
            'label' => 'Nom',
            'label_attr' => [
                'class' => 'text-light'
            ],
        ])
        ->add('description', TextareaType::class, [
            'attr' => [
                'maxlength' => '255'
            ],
            'label' => 'Description',
            'label_attr' => [
                'class' => 'text-light'
            ],
        ])
            ->add('date', DateTimeType::class,[
                'widget' => 'single_text',
                'label' => 'Date de formation',
                'label_attr' => [
                    'class' => 'text-light'
                ],
            ])
            ->add('nbr_Place', IntegerType::class, [
                'label' => 'Nombre de Place',
                'label_attr' => [
                    'class' => 'text-light'
                ],
                'attr' => [
                    'min' => 1, // This sets a minimum value on the input field itself
                    'step' => 1, // Ensures input steps in integers only
                ],
            ])

            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'label_attr' => [
                    'class' => 'text-light'
                ],
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
            ])

            ->add('submit', SubmitType::class,[
                'label' => 'Submit',
                'attr' => [
                    'class' => 'mt-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
