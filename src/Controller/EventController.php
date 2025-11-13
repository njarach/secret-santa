<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Security\Voter\AccessVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    private ParticipantRepository $participantRepository;

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    #[Route('/event/{id}/leave-event', name: 'app_event_leave')]
    public function leaveEvent(int $id): Response
    {
        $participant = $this->participantRepository->find($id);
        if (!$participant) {
            $this->addFlash('error', "Le participant n'existe pas");

            return $this->redirectToRoute('app_home');
        }
        $event = $participant->getEvent();
        $this->denyAccessUnlessGranted(AccessVoter::PARTICIPANT_ACCESS, $event);
        $event->removeParticipant($participant);
        $this->addFlash('success', "Vous avez quitté l'évènement.");
        return $this->redirectToRoute('app_home');
    }
}
