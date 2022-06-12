<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CertificateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Certificate::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            NumberField::new('number_of_uses'),
            NumberField::new('used'),
            TextField::new('code'),
            NumberField::new('value'),
            BooleanField::new('is_active'),
            AssociationField::new('used_users'),
        ];
    }

}
