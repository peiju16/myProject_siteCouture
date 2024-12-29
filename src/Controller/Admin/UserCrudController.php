<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
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

            TextField::new('email')
                ->setLabel('Email Address')
                ->setRequired(true)
                ->hideOnForm(),

            ArrayField::new('roles')
                ->setLabel('Roles'),

            TextField::new('lastName')
                ->setLabel('Last Name')
                ->setRequired(true),

            TextField::new('firstName')
                ->setLabel('First Name')
                ->setRequired(true),

            TextField::new('pseudo')
                ->setLabel('Pseudo')
                ->hideOnIndex(),

            TextField::new('telephone')
                ->setLabel('Phone Number')
                ->setRequired(true),

            TextField::new('address')
                ->setLabel('Address')
                ->hideOnIndex(),

            TextField::new('city')
                ->setLabel('City')
                ->hideOnIndex(),

            TextField::new('zipCode')
                ->setLabel('Zip Code')
                ->hideOnIndex(),

            ImageField::new('imageName')
                ->setBasePath('/images/users')
                ->setUploadDir('public/images/users')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->onlyOnIndex(),

            TextField::new('imageFile')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),

            DateTimeField::new('createdAt')
                ->setLabel('Created At')
                ->hideOnForm(),

            BooleanField::new('isVerifed')
                ->setLabel('Verified')
                ->renderAsSwitch(false)
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
