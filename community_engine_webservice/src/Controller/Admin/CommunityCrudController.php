<?php

namespace App\Controller\Admin;

use App\Entity\Community;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CommunityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Community::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextField::new('url'),
            ChoiceField::new('isPrivate')->setChoices([
                'Yes' => 1,
                'No' => 0,
            ])->setRequired(true),
            ChoiceField::new('isDefault')->setChoices([
                'No' => 0,
                'Yes' => 1,
            ])->setRequired(true),
            ChoiceField::new('isPaid')->setChoices([
                'Yes' => 1,
                'No' => 0,
            ])->setRequired(true),
            AssociationField::new('users')
                ->onlyOnIndex(),
            AssociationField::new('managers')
                ->onlyWhenUpdating()
                ->onlyWhenCreating(),
            TextEditorField::new('description')
                ->setRequired(true),
            TextEditorField::new('short_description'),
            AssociationField::new('questions')
                ->setFormTypeOption('by_reference', false),
            ImageField::new('logo')
                ->setRequired(false)
                ->setBasePath('/upload/communities')
                ->setUploadedFileNamePattern('[uuid]-[contenthash].[extension]')
                ->setUploadDir('/public/upload/communities'),
        ];
    }

}
