<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC']);
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('status')
            ->add('isPaid')
            ->add('createdAt');
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Title'),
            TextField::new('receiverName', 'Receiver Name'),
            EmailField::new('receiverEmail', 'Receiver Email'),
            TextField::new('receiverPhone', 'Receiver Phone'),
            TextareaField::new('receiverAddress', 'Address')->hideOnIndex(),
            TextField::new('city', 'City'),
            TextField::new('zipCode', 'ZIP Code'),
            DateTimeField::new('createdAt', 'Created At')->setSortable(true)->hideOnForm(),
            AssociationField::new('transportWay', 'Transport Way'),
            MoneyField::new('transportPrice', 'Transport Price')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            BooleanField::new('isPaid', 'Is Paid')->hideOnForm(),
            TextField::new('paymentMethod', 'Payment Method')->hideOnForm(),
            TextField::new('reference', 'Reference')->hideOnForm(),
            TextField::new('status', 'Status'),
            MoneyField::new('totalPrice', 'Total Price')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            AssociationField::new('user', 'User')->hideOnForm(),
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
