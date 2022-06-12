<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Security\TokenAuthenticator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserCrudController extends AbstractCrudController
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * UserCrudController constructor.
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        if ($pageName == 'index') {
            return [
                IdField::new('id'),
                TextField::new('fullName'),
                TextField::new('email')
                    ->formatValue(function ($value, User $user) {
                        return $user->getActualEmail();
                    }),
                CollectionField::new('communities'),
                BooleanField::new('profile_complete'),
//                BooleanField::new('readyToMatch'),
//                BooleanField::new('doNotDisturb'),
                BooleanField::new('isUseTelegram')
                    ->renderAsSwitch(false),
                AssociationField::new('invitedBy'),
                AssociationField::new('invitedToCommunity')
                    ->setLabel('Invited to'),
                AssociationField::new('myInvitedUsers')
                    ->setLabel('Invite'),
//                    ->setFormTypeOption('by_reference', false),
//                NumberField::new('balance'),
            ];
        }
        return [
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('email'),
            TextField::new('emailAlt'),
            TextField::new('facebookLink'),
            TextField::new('linkedinLink'),
            TextField::new('TelegramUsername'),
            BooleanField::new('profileComplete'),

            BooleanField::new('questionComplete'),
            BooleanField::new('readyToMatch'),
            BooleanField::new('doNotDisturb'),

            TextareaField::new('about'),
            TextareaField::new('looking_for'),
            AssociationField::new('communities')
                ->setFormTypeOption('by_reference', false),
            AssociationField::new('manageCommunities')
                ->setFormTypeOption('by_reference', false),
            ChoiceField::new('roles')
                ->allowMultipleChoices()
                ->setChoices([
                    'ROLE_USER' => 'ROLE_USER',
                    'ROLE_COMMUNITY_MANAGER' => 'ROLE_COMMUNITY_MANAGER',
                ]),
            AssociationField::new('answers')
                ->setFormTypeOption('by_reference', false),
            TextField::new('publicId'),
            Field::new('telegramId'),
            DateTimeField::new('createdAt')
                ->onlyOnDetail(),
            AssociationField::new('invitedBy')
                ->onlyOnDetail(),
            CollectionField::new('myInvitedUsers')
                ->onlyOnDetail(),
            TextField::new('tempToken')
                ->formatValue(function ($value) {
                    return $this->urlGenerator->generate('redirect_auth', [
                        's' => (string)$value,
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                })
                ->onlyOnDetail()
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(
            ['id' => 'DESC']
        );
    }

}
