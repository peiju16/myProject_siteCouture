<?php

namespace App\Controller\Admin;

use App\Entity\Invoice;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class InvoiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Facture')
            ->setEntityLabelInPlural('Factures')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('createdAt');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'Invoice ID')->onlyOnIndex(),
            AssociationField::new('orderInvoice', 'Order')
                ->setCrudController(OrderCrudController::class)
                ->onlyOnIndex(),
            AssociationField::new('user', 'User')
                ->onlyOnIndex(),
            BooleanField::new('isPdf', 'Is PDF Generated'),
            UrlField::new('pdfPath', 'PDF File')
                ->setLabel('View PDF')
                ->formatValue(static function (?string $value) {
                    if ($value) {
                        $publicPath = '/factures/' . basename($value); // Adjust path to public/factures
                        return sprintf('<a href="%s" target="_blank">View PDF</a>', $publicPath);
                    }
                    return 'No PDF';
                })
                ->onlyOnIndex(),
            DateTimeField::new('createdAt', 'Created At')->onlyOnIndex(),
            DateTimeField::new('UpdateAt', 'Updated At')->onlyOnIndex(),
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
