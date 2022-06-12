<?php

namespace App\Validator;

use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AnswerValidator
 * @package App\Validator
 * @Annotation
 */
class AnswerValidator extends ConstraintValidator
{
    /*
     * Fix for old user
     */
    const OLD_USERS_DT = '2020-10-23 00:00';

    /**
     * @var Security
     */
    private $security;

    /**
     * AnswerValidator constructor.
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param ArrayCollection $answers
     * @param Constraint $constraint
     */
    public function validate($answers, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\Answer */
        /** @var User $user */
        $user = $this->security->getUser();
        $old = new \DateTime(self::OLD_USERS_DT);
        if ($user instanceof User and $user->getCreatedAt() < $old) {
            return;
        }

        $count = [];
        $answers->map(function (\App\Entity\Answer $answer) use (&$count) {
            if ($answer->getQuestion()) {
                $count[$answer->getQuestion()->getTitle()] =
                    isset($count[$answer->getQuestion()->getTitle()]) ?
                        ++$count[$answer->getQuestion()->getTitle()] : 1;
            }
        });

        foreach ($count as $title => $c) {
            if ($c > 3) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ question }}', $title)
                    ->addViolation();
            }
        }
    }
}
