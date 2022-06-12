<?php

namespace App\Controller\Admin;

use App\Entity\Call;
use App\Entity\Review;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReviewCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName == 'index') {
            return [
                DateTimeField::new('created_at'),
                TextField::new('user.fullName'),
                TextField::new('content')
                    ->setMaxLength(999999999),
                BooleanField::new('isSuccessful')
                    ->renderAsSwitch(false),
                NumberField::new('rate'),
                TextField::new('connect')
                    ->renderAsHtml(true)
                    ->setMaxLength(9999999)
                    ->formatValue(function ($value) {
                        return nl2br($value);
                    }),
            ];
        }
        return [
            AssociationField::new('user'),
            AssociationField::new('connect'),
            TextEditorField::new('content'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(
            ['created_at' => 'DESC']
        );
    }
}
