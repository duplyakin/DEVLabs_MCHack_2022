<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class UserType extends AbstractType
{
    /**
     * @var Security
     */
    private $security;

    /**
     * UserType constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->security->getUser() && empty($this->security->getUser()->getEmail())) {
            $builder
                ->add('emailAlt', null, [
                    'label' => 'E-mail',
                ]);
        }

        $builder
            ->add('firstName')
            ->add('lastName')
//            ->add('telegramUsername')
            ->add('facebookLink')
            ->add('linkedinLink')
            ->add('calendlyLink')
            ->add('about', TextareaType::class, [
                'label' => 'What is your expertise?',
                'help' => 'Current place of work, skills, experience',
            ])
//            ->add('looking_for', TextareaType::class)
            ->add('save', SubmitType::class)
//            ->add('answers', CollectionType::class, [
//                'entry_type' => AnswerType::class,
//                'entry_options' => ['label' => false],
//            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => ['create_profile'],
        ]);
    }
}
