<?php
namespace App\Form;

use App\Entity\Order;
use App\Entity\Transport;
use App\Entity\TransportAddress;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user']; // Passed from controller

        $builder
            ->add('transportWay', EntityType::class, [
                'class' => Transport::class,
                'choice_label' => function (Transport $transport) {
                    return sprintf('%s - %s (€%.2f)', $transport->getTitle(), $transport->getContent(), $transport->getPrice());
                },
                'choice_attr' => function (Transport $transport) {
                    return ['data-is-pickup' => $transport->getIsPickup() ? 'true' : 'false'];
                },
                'placeholder' => 'Choisir un mode de livraison',
                'label' => 'Mode de livraison',
            ])
        
            ->add('title', TextType::class, [
                'label' => 'Title',
            ])
            ->add('receiverName', TextType::class, [
                'label' => 'Nom de déstinataire',
            ])
            ->add('receiverEmail', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('receiverPhone', TextType::class, [
                'label' => 'Numéro de téléphone',
            ])
            ->add('useSavedAddress', CheckboxType::class, [
                'label' => 'Utiliser un adresse enregistré',
                'required' => false,
                'mapped' => false,
            ])
            ->add('savedAddress', EntityType::class, [
                'class' => TransportAddress::class,
                'choices' => $user->getTransportAddresses(),
                'choice_label' => function (TransportAddress $address) {
                    return sprintf('%s - %s, %s, %s', $address->getTitle(), $address->getAddress(), $address->getCity(), $address->getZipCode());
                },
                'placeholder' => 'Choisir un addresse',
                'required' => false,
                'mapped' => false,
                'label' => false,
            ])
            ->add('addressTitle', TextType::class, [
                'label' => 'title de cet addresse',
                'required' => false,
                'mapped' => false,
            ])
            ->add('receiverAddress', TextType::class, [
                'label' => 'Addresse',
            ])
            ->add('city', TextType::class, [
                'label' => 'ville',
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Code Postal',
            ])
            ->add('saveAddress', CheckboxType::class, [
                'label' => 'Sauvgarder cet addresse pour la prochaine fois',
                'required' => false,
                'mapped' => false,
            ])
       ;     
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'user' => null, // Pass the logged-in user
        ]);
    }
}
