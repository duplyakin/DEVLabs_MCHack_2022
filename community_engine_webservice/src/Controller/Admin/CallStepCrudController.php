<?php

namespace App\Controller\Admin;

use App\Entity\CallStep;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CallStepCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CallStep::class;
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
