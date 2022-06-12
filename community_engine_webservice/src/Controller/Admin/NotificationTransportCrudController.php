<?php

namespace App\Controller\Admin;

use App\Entity\Notification\NotificationTransport;
use App\Entity\Notification\NotificationTransportNode;
use App\Form\Admin\NotificationBodyField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Class NotificationTransportCrudController
 * @package App\Controller\Admin
 */
class NotificationTransportCrudController extends AbstractCrudController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * NotificationTransportCrudController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return NotificationTransport::class;
    }

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            CollectionField::new('meta'),
            AssociationField::new('node')
                ->setLabel('Transport')
                ->setRequired(true),
            AssociationField::new('community'),
            AssociationField::new('notification')
                ->setRequired(true),
            TextareaField::new('body')
                ->setFormTypeOptions([
                    'block_name' => 'notification_body',
                ])
                ->onlyOnForms(),
        ];
    }

    /**
     * @param Crud $crud
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // don't forget to add EasyAdmin's form theme at the end of the list
            // (otherwise you'll lose all the styles for the rest of form fields)
            ->setFormThemes(['admin/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig']);
    }

}
