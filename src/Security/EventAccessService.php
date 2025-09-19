<?php

namespace App\Security;

use App\Entity\Participant;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

readonly class EventAccessService
{
    public function __construct(
        private EventRepository $eventRepository,
        private ParticipantRepository $participantRepository,
        private RequestStack $requestStack,
    ) {
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function authenticateUser(int $id, string $accessToken): string|false
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            return false;
        }

        $session = $this->getSession();
        $session->set('event_id', $event->getId());

        if ($accessToken === $event->getAdminAccessToken()) {
            $session->set('user_role', 'admin');
            $session->set('user_token', $accessToken);

            return 'admin';
        }

        $participant = $this->participantRepository->findOneByEventAndToken($event, $accessToken);
        if ($participant && $accessToken === $participant->getEventAccessToken()) {
            $session->set('user_role', 'participant');
            $session->set('user_token', $accessToken);

            return 'participant';
        }

        return false;
    }

    public function checkAuthentication(int $requiredEventId, string $requiredRole): bool
    {
        $session = $this->getSession();
        $eventId = $session->get('event_id');
        $token = $session->get('user_token');
        $role = $session->get('user_role');

        if (!$token) {
            return false;
        }

        if ($eventId != $requiredEventId || $role != $requiredRole) {
            return false;
        }

        return $this->validateToken($token, $role, $eventId);
    }

    private function validateToken(mixed $token, string $role, mixed $eventId): bool
    {
        $event = $this->eventRepository->find($eventId);
        if (!$event) {
            return false;
        }

        if ('admin' === $role) {
            return $token === $event->getAdminAccessToken();
        } elseif ('participant' === $role) {
            $participant = $this->participantRepository->findOneByEventAndToken($event, $token);
            if (!$participant) {
                return false;
            }

            return $participant->getEventAccessToken() === $token;
        }

        return false;
    }

    public function getCurrentParticipant(): ?Participant
    {
        $session = $this->getSession();
        $eventId = $session->get('event_id');
        $token = $session->get('user_token');
        $role = $session->get('user_role');

        if (!$token || $eventId || 'participant' !== $role) {
            return null;
        }
        $event = $this->eventRepository->find($eventId);
        return $this->participantRepository->findOneByEventAndToken($event, $token);
    }

    public function emptyAccess(): void
    {
        $session = $this->getSession();
        $session->remove('event_id');
        $session->remove('user_token');
        $session->remove('user_role');
    }
}
