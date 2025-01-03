<?php

namespace App\Controller\Admin;

use App\Entity\ClientContact;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ClientContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClientContact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            AssociationField::new('client', 'Client'),

            ChoiceField::new('title')
            ->setLabel('Type de Contact')
            ->setRequired(true)
            ->setChoices([
                'Siége' => 'siége',
                'Comptable' => 'comptable',
                'Directeur(trise)' => 'manager',
                'Boutique' => 'boutique'
            ]),

            TextField::new('lastName')
                ->setLabel('Nom')
                ->setRequired(true),

            TextField::new('firstName')
                ->setLabel('Prénom')
                ->setRequired(true),

            TextField::new('telephone')
                ->setLabel('Numéro de téléphone')
                ->setRequired(true),
            
                        
            TextField::new('email')
            ->setLabel('E-mail')
            ->setRequired(true),

            TextField::new('address')
                ->setLabel('Addresse')
                ->setRequired(true),

            TextField::new('city')
                ->setLabel('Ville')
                ->setRequired(true),

            TextField::new('zipCode')
                ->setLabel('Code postale')
                ->setRequired(true),

           
            DateTimeField::new('createdAt')
                ->setLabel('Created At')
                ->hideOnForm(),

        ];
}

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
