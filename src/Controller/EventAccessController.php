<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventAccessController extends AbstractController
{
    private EventRepository $eventRepository;
    private ParticipantRepository $participantRepository;

    public function __construct(EventRepository $eventRepository, ParticipantRepository $participantRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->participantRepository = $participantRepository;
    }

    #[Route('/event/access/{id}/{token}', name: 'app_event_access')]
    public function index(int $id, string $token): Response
    {
        /* TODO : this will be used to redirect user to the page they need depending on the link they used (found in their access mail) */
        $event = $this->eventRepository->find($id);

        if (!isset($event)) {
            throw $this->createNotFoundException("L'évènement n'existe pas.");
        }

        if ($token === $event->getAdminAccessToken()) {
            return $this->redirectToRoute('app_event_admin_dashboard', ['id' => $event->getId(), 'token' => $event->getAdminAccessToken()]);
        }

        if ($token === $event->getPublicJoinToken()) {
            return $this->redirectToRoute('app_event_join_event', ['id' => $event->getId(), 'token' => $event->getPublicJoinToken()]);
        }

        $participant = $this->participantRepository->findOneByEventAndToken($event, $token);
        if ($token === $participant->getEventAccessToken()) {
            return $this->redirectToRoute('app_event_participant_dashboard', ['id' => $event->getId(), 'token' => $participant->getEventAccessToken()]);
        }

        throw new AccessDeniedException('Le token est invalide, ou la page que vous cherchez n\'est pas accessible.');
    }

    #[Route('/event/{id}/{token}/admin-dashboard', name: 'app_event_admin_dashboard')]
    public function adminDashboard(int $id, string $token): Response
    {
        return new Response('Admin Dashboard', Response::HTTP_OK);
    }

    #[Route('/event/{id}/{token}/participant-dashboard', name: 'app_event_participant_dashboard')]
    public function participantDashboard(int $id, string $token): Response
    {
        return new Response('Participant Dashboard', Response::HTTP_OK);
    }

    #[Route('/event/{id}/{token}/join-event', name: 'app_event_join_event')]
    public function joinEvent(int $id, string $token): Response
    {
        return new Response('Join event page, accessed with the invite sent by the event creator. New participant fills form and submitting confirms participation, redirected to user dashboard.', Response::HTTP_OK);
    }
}
