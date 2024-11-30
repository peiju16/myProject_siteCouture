<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PositiveOrZero;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'minlength' => '2',
                    'maxlength' => '50'
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
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'label_attr' => [
                    'class' => 'text-light'
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'label_attr' => [
                    'class' => 'text-light'
                ],
                'attr' => [
                    'min' => 1, // This sets a minimum value on the input field itself
                    'step' => 1, // Ensures input steps in integers only
                ],
            ])

            ->add('productImages', CollectionType::class, [
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => false,
                'entry_options' => ['label' => false],
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
            'data_class' => Product::class,
        ]);
    }
}
