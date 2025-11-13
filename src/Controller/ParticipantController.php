<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Security\EventAccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ParticipantController extends AbstractController
{
    private ParticipantRepository $participantRepository;
    private EntityManagerInterface $entityManager;
    private EventAccessService $eventAccessService;

    public function __construct(
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        EventAccessService $eventAccessService,
    ) {
        $this->participantRepository = $participantRepository;
        $this->entityManager = $entityManager;
        $this->eventAccessService = $eventAccessService;
    }

    #[Route('/participant/{id}/update-name', name: 'app_participant_update_name', methods: ['POST'])]
    public function updateName(int $id, Request $request): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant non trouvé');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $currentParticipant = $this->eventAccessService->getCurrentParticipant();
        if (!$currentParticipant || $currentParticipant->getId() !== $participant->getId()) {
            $this->addFlash('error', 'Non autorisé');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $name = trim($request->request->get('name', ''));

        if (empty($name)) {
            $this->addFlash('error', 'Le nom ne peut pas être vide');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $participant->setName($name);
        $this->entityManager->flush();

        $this->addFlash('success', 'Nom mis à jour avec succès');

        return $this->render('event/participant_dashboard.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]);
    }

    #[Route('/participant/{id}/update-wishlist', name: 'app_participant_update_wishlist', methods: ['POST'])]
    public function updateWishlist(int $id, Request $request): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant non trouvé');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $currentParticipant = $this->eventAccessService->getCurrentParticipant();
        if (!$currentParticipant || $currentParticipant->getId() !== $participant->getId()) {
            $this->addFlash('error', 'Non autorisé');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $wishlist = trim($request->request->get('wishlist', ''));

        $participant->setWishlist($wishlist);
        $this->entityManager->flush();

        $this->addFlash('success', 'Liste de souhaits enregistrée');

        return $this->render('event/participant_dashboard.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]);
    }

    #[Route('/participant/{id}/add-exclusion', name: 'app_participant_add_exclusion', methods: ['POST'])]
    public function addExclusion(int $id, Request $request): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant introuvable.');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $currentParticipant = $this->eventAccessService->getCurrentParticipant();
        if (!$currentParticipant || $currentParticipant->getId() !== $participant->getId()) {
            $this->addFlash('error', 'Non autorisé');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $newExclusion = trim($request->request->get('exclusion', ''));

        if (empty($newExclusion)) {
            return $this->render('event/participant_dashboard.html.twig', [
                'participant' => $participant,
                'event' => $participant->getEvent(),
            ]);
        }

        $exclusions = $participant->getExclusions() ?? [];

        if (!in_array($newExclusion, $exclusions)) {
            $exclusions[] = $newExclusion;
            $participant->setExclusions($exclusions);
            $this->entityManager->flush();
        }

        return $this->render('event/participant_dashboard.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]);
    }

    #[Route('/participant/{id}/remove-exclusion', name: 'app_participant_remove_exclusion', methods: ['POST'])]
    public function removeExclusion(int $id, Request $request): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant introuvable.');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $currentParticipant = $this->eventAccessService->getCurrentParticipant();
        if (!$currentParticipant || $currentParticipant->getId() !== $participant->getId()) {
            $this->addFlash('error', 'Non autorisé');

            return $this->render('event/htmx_partials/_participant_flash_messages.html.twig');
        }

        $exclusionToRemove = $request->request->get('exclusion', '');

        $exclusions = $participant->getExclusions() ?? [];
        $exclusions = array_filter($exclusions, fn ($e) => $e !== $exclusionToRemove);
        $exclusions = array_values($exclusions);

        $participant->setExclusions($exclusions);
        $this->entityManager->flush();

        return $this->render('event/participant_dashboard.html.twig', [
            'participant' => $participant,
            'event' => $participant->getEvent(),
        ]);
    }
}
