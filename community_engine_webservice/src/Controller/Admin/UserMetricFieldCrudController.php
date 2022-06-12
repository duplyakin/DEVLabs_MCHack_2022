<?php

namespace App\Controller\Admin;

use App\Entity\UserMetricField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserMetricFieldCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserMetricField::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('label'),
            ChoiceField::new('type')->setChoices([
                'Boolean' => UserMetricField::TYPE_BOOLEAN,
                'Float' => UserMetricField::TYPE_FLOAT,
            ])->setRequired(true),
            NumberField::new('multiplier'),
        ];
    }
}
