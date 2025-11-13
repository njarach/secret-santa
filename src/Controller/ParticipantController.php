<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Security\EventAccessService;
use App\Security\Voter\AccessVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipantController extends AbstractController
{
    private ParticipantRepository $participantRepository;
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;
    private EventAccessService $eventAccessService;

    public function __construct(
        ParticipantRepository $participantRepository,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
        EventAccessService $eventAccessService,
    ) {
        $this->participantRepository = $participantRepository;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->eventAccessService = $eventAccessService;
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

    #[Route('/participant/{id}/update-name', name: 'app_participant_update_name', methods: ['POST'])]
    public function updateName(int $id, Request $request): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant non trouvé');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $currentParticipant = $this->eventAccessService->getCurrentParticipant();
        if (!$currentParticipant || $currentParticipant->getId() !== $participant->getId()) {
            $this->addFlash('error', 'Non autorisé');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $name = trim($request->request->get('name', ''));

        if (empty($name)) {
            $this->addFlash('error', 'Le nom ne peut pas être vide');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $participant->setName($name);
        $this->entityManager->flush();

        return new Response($this->renderView('event/participant_dashboard.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]));
    }

    #[Route('/participant/{id}/update-wishlist', name: 'app_participant_update_wishlist', methods: ['POST'])]
    public function updateWishlist(int $id, Request $request): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant non trouvé');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $currentParticipant = $this->eventAccessService->getCurrentParticipant();
        if (!$currentParticipant || $currentParticipant->getId() !== $participant->getId()) {
            $this->addFlash('error', 'Non autorisé');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $wishlist = trim($request->request->get('wishlist', ''));

        $participant->setWishlist($wishlist);
        $this->entityManager->flush();

        return new Response($this->renderView('event/participant_dashboard.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]));
    }

    #[Route('/event/{id}/leave-event', name: 'app_event_leave')]
    public function leaveEvent(int $id): Response
    {
        $participant = $this->participantRepository->find($id);
        if (!$participant) {
            $this->addFlash('error', "Le participant n'existe pas");

            return $this->render('partials/_swap_flash_message.html.twig');
        }
        $event = $participant->getEvent();
        $this->denyAccessUnlessGranted(AccessVoter::PARTICIPANT_ACCESS, $event);
        $event->removeParticipant($participant);

        return new Response($this->renderView('event/participant_dashboard.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]));
    }
}
