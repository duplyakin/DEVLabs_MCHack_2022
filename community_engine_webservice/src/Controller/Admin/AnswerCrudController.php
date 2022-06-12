<?php

namespace App\Controller\Admin;

use App\Entity\Answer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AnswerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Answer::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            AssociationField::new('question')->setRequired(true),
            TextField::new('icon')
                ->setHelp('Use ico name from <a href="https://themify.me/themify-icons" target="_blank">https://themify.me/themify-icons</a>'),
            AssociationField::new('relatedAnswers')
                ->setFormTypeOption('by_reference', false),
        ];
    }

}
