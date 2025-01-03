<?php

namespace App\Form;

use App\Entity\ClientFacture;
use App\Entity\ClientContact;
use App\Entity\Service;
use App\Repository\ServiceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ClientFactureType extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, private ServiceRepository $serviceRepository)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('client')
            ->add('contact', EntityType::class, [
                'class' => ClientContact::class,
                'choice_label' => 'title',
                'placeholder' => 'Select Contact',
                'empty_data' => null,
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'title', // Display the service title
                'multiple' => true,
                'expanded' => true, // Render as checkboxes
                'choice_attr' => function (Service $service) {
                    return ['data-price' => $service->getPrice()]; // Add data-price attribute
                },
            ])
            
            ->add('totalPrice', MoneyType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => ['class' => 'mt-3'],
            ]);
        }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClientFacture::class,
        ]);
    }
}
