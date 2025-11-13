<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Security\Voter\AccessVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminEventController extends AbstractController
{
    private ParticipantRepository $participantRepository;
    private EventRepository $eventRepository;

    public function __construct(ParticipantRepository $participantRepository, EventRepository $eventRepository)
    {
        $this->participantRepository = $participantRepository;
        $this->eventRepository = $eventRepository;
    }

    #[Route('/event/{id}/admin-dashboard', name: 'app_event_admin_dashboard')]
    public function adminDashboard(int $id): Response
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException("Cet event n'existe pas.");
        }

        $this->denyAccessUnlessGranted(AccessVoter::ADMIN_ACCESS, $event);

        $participants = $this->participantRepository->findBy(['event' => $event], ['createdAt' => 'ASC']);

        return $this->render('event/admin/admin_dashboard.html.twig', [
            'event' => $event,
            'participants' => $participants,
        ]);
    }
}
