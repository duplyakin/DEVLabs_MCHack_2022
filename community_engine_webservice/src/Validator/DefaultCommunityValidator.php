<?php

namespace App\Validator;

use App\Entity\Community;
use App\Repository\CommunityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DefaultCommunityValidator extends ConstraintValidator
{
    /**
     * @var CommunityRepository
     */
    private $repository;

    /**
     * DefaultCommunityValidator constructor.
     * @param CommunityRepository $repository
     */
    public function __construct(CommunityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Community $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value->getIsDefault()) {
            return;
        }

        /** @var Community $default */
        $default = $this->repository->findOneBy([
            'is_default' => 1,
        ]);

        /* @var $constraint \App\Validator\DefaultCommunity */
        if ($default && $value->getId() != $default->getId()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
