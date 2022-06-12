<?php

namespace App\Controller\Admin;

use App\Entity\UserAdmin;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserAdminCrudController
 * @package App\Controller\Admin
 */
class UserAdminCrudController extends AbstractCrudController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserAdminCrudController constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return UserAdmin::class;
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserAdmin $user
     */
    public function persistEntity(EntityManagerInterface $entityManager, $user): void
    {
        $encodedPassword = $this->encoder
            ->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encodedPassword);
        $user->setRoles([
            'ROLE_ADMIN'
        ]);

        parent::persistEntity($entityManager, $user);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param UserAdmin $user
     */
    public function updateEntity(EntityManagerInterface $entityManager, $user): void
    {
        if ($user->getPlainPassword()) {
            $encodedPassword = $this->encoder
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encodedPassword);
        }
        parent::updateEntity($entityManager, $user);
    }

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('email'),
            TextField::new('plainPassword')
                ->setLabel('Password')
                ->setRequired(true),
        ];
    }

}
