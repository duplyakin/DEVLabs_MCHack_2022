<?php

namespace App\Controller\Admin;

use App\Entity\Question;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class QuestionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextField::new('declarativeTitle'),
            ChoiceField::new('multiple')->setChoices([
                'Yes' => 1,
                'No' => 0,
            ]),
            ChoiceField::new('tag')->setChoices([
                'Profile fill first screen' => Question::TAG_PROFILE_FILL_FIRST_SCREEN,
                'Profile fill info screen' => Question::TAG_PROFILE_FILL_INFO_SCREEN,
                'Profile new call' => Question::TAG_PROFILE_NEW_CALL,
            ]),
            AssociationField::new('communities')
                ->setFormTypeOption('by_reference', false),
            IntegerField::new('position'),
        ];
    }

}
