<?php

namespace App\Controller\Admin;

use App\Entity\PriorityMetricKeyword;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PriorityMetricKeywordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PriorityMetricKeyword::class;
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
