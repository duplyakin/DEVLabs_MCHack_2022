<?php

namespace App\Controller\Admin;

use App\Entity\Call;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CallCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Call::class;
    }


    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            DateField::new('createdAt'),
//            DateField::new('callDate'),
            TextField::new('userList')
                ->setVirtual(true)
                ->renderAsHtml(true)
                ->formatValue(function ($value, $entity) {
                    return implode("<br/>", $entity->getUsers()->getValues());
                })->hideOnForm(),
            ArrayField::new('community'),
        ];

        if ($pageName == Action::EDIT) {
            $fields[] = AssociationField::new('users');
        }

        return $fields;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('created_at')
            ->add('community');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(
            ['id' => 'DESC']
        );
    }

}
