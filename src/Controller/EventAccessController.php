<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Security\EventAccessService;
use App\Security\Voter\AccessVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class EventAccessController extends AbstractController
{
    private EventRepository $eventRepository;
    private EventAccessService $eventAccessService;

    public function __construct(EventRepository $eventRepository, EventAccessService $eventAccessService)
    {
        $this->eventRepository = $eventRepository;
        $this->eventAccessService = $eventAccessService;
    }

    #[Route('/event/access/{id}/{token}', name: 'app_event_access')]
    public function index(int $id, string $token): Response
    {
        $authentication = $this->eventAccessService->authenticateUser($id, $token);

        if (!$authentication) {
            throw new AccessDeniedException("Le token n'est pas valide.");
        }

        return match ($authentication) {
            'admin' => $this->redirectToRoute('app_event_admin_dashboard', ['id' => $id]),
            'participant' => $this->redirectToRoute('app_event_participant_dashboard', ['id' => $id]),
            default => throw new AccessDeniedException('Accès refusé, aucun participant ou administrateur trouvé pour ce token et cet évènement.'),
        };
    }

    #[Route('/event/{id}/admin-dashboard', name: 'app_event_admin_dashboard')]
    public function adminDashboard(int $id): Response
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException("Cet event n'existe pas.");
        }

        $this->denyAccessUnlessGranted(AccessVoter::ADMIN_ACCESS, $event);

        return $this->render('event/admin/admin_dashboard.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/participant-dashboard', name: 'app_event_participant_dashboard')]
    public function participantDashboard(int $id): Response
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException("Cet event n'existe pas.");
        }

        $this->denyAccessUnlessGranted(AccessVoter::PARTICIPANT_ACCESS, $event);

        return $this->render('event/participant_dashboard.html.twig', [
            'event' => $event,
            'participant' => $this->eventAccessService->getCurrentParticipant(),
        ]);
    }
}
