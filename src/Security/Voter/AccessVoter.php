<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Security\EventAccessService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class AccessVoter extends Voter
{
    public const string ADMIN_ACCESS = 'ADMIN_ACCESS';
    public const string PARTICIPANT_ACCESS = 'PARTICIPANT_ACCESS';
    private EventAccessService $eventAccessService;

    public function __construct(
        EventAccessService $eventAccessService,
    ) {
        $this->eventAccessService = $eventAccessService;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::ADMIN_ACCESS, self::PARTICIPANT_ACCESS])
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /* @var Event $subject */

        return match ($attribute) {
            self::ADMIN_ACCESS => $this->eventAccessService->checkAuthentication($subject->getId(), 'admin'),
            self::PARTICIPANT_ACCESS => $this->eventAccessService->checkAuthentication($subject->getId(), 'participant'),
            default => false,
        };
    }
}
