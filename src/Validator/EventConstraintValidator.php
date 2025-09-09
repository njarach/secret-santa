<?php

namespace App\Validator;

use App\Repository\EventRepository;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EventConstraintValidator extends ConstraintValidator
{

    public function __construct(
        private readonly EventRepository $eventRepository
    ) {}

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EventConstraint) {
            throw new UnexpectedTypeException($constraint, EventConstraint::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $numberOfEvents = $this->eventRepository->findBy(['adminEmail' => $value->getAdminEmail()]);
        if (count($numberOfEvents) >= 3) {
            $this->context->buildViolation($constraint->adminHasTooManyEvents)->addViolation();
        }
        $adminLastEvent = $this->eventRepository->findOneBy(['adminEmail' => $value->getAdminEmail(),['id'=>'desc']]);
        if ($adminLastEvent) {
            $tenMinutesAgo = new \DateTimeImmutable('-10 minutes');
            if ($adminLastEvent->getCreatedAt() > $tenMinutesAgo) {
                $this->context->buildViolation($constraint->tooManySuccessiveEvents)->addViolation();
            }
        }
    }
}
