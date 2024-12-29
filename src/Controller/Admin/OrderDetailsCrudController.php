<?php

namespace App\Controller\Admin;

use App\Entity\OrderDetails;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;

class OrderDetailsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderDetails::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande en Détail')
            ->setEntityLabelInPlural('Commandes en Détail')
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('product', 'Product ID')
            ->formatValue(static function ($value, $entity) {
                // Customize to show the product ID only
                return implode(', ', $entity->getProduct()->map(fn($product) => $product->getId())->toArray());
            }),
            NumberField::new('quantity', 'Quantity'),
            MoneyField::new('price', 'Price')
                ->setCurrency('EUR')
                ->setStoredAsCents(false),
            TextField::new('productName', 'Product Name'),
            AssociationField::new('orderNumber', 'Order')
                ->autocomplete(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::EDIT, Action::DELETE, Action::NEW) // Disable edit, delete, and create
            ->add(Crud::PAGE_INDEX, Action::DETAIL);// Ensure the detail action is available
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
