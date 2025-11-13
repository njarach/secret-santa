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
    private EventAccessService $eventAccessService;

    public function __construct(EventAccessService $eventAccessService)
    {
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
}
