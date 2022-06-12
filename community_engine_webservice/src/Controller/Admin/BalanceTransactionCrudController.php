<?php

namespace App\Controller\Admin;

use App\Entity\BalanceTransaction;
use App\Service\Payment\Deposit\Certificate;
use App\Service\Payment\Spend\Connect;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class BalanceTransactionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BalanceTransaction::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName == 'edit') {
            return [];
        }
        return [
            DateTimeField::new('created_at'),
            AssociationField::new('user')
                ->setSortable(false),
            NumberField::new('value'),
            TextField::new('class_name'),
            TextField::new('description')
                ->setMaxLength(99999999),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add(
                ChoiceFilter::new('class_name')
                    ->canSelectMultiple(true)
                    ->setChoices([
                        'Certificate Deposit' => Certificate::class,
                        'Connect Spend' => Connect::class,
                    ])
            );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::DELETE, Action::EDIT);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(
            ['created_at' => 'DESC']
        );
    }
}
