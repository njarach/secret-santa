<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
#[\Attribute]
class EventConstraint extends Constraint
{
    public string $adminHasTooManyEvents = 'Vous avez déjà 3 événements en cours. Veuillez supprimer un événement pour en créer un nouveau.';
    public string $tooManySuccessiveEvents = 'Vous avez déjà créé un événement. Veuillez patienter 10 minutes avant de créer un nouvel event.';

    public function __construct(?string $adminHasTooManyEvents = null, ?string $tooManySuccessiveEvents = null, ?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->adminHasTooManyEvents = $adminHasTooManyEvents ?? $this->adminHasTooManyEvents;
        $this->tooManySuccessiveEvents = $tooManySuccessiveEvents ?? $this->tooManySuccessiveEvents;
    }
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
