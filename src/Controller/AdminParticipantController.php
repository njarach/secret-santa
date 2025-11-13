<?php

namespace App\Controller;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminParticipantController extends AbstractController
{
    private ParticipantRepository $participantRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        $this->participantRepository = $participantRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/participant/{id}/add-exclusion', name: 'app_participant_add_exclusion', methods: ['POST'])]
    public function addExclusion(int $id, Request $request): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant introuvable.');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $excludedParticipantId = (int) $request->request->get('excluded_participant_id');

        if (!$excludedParticipantId) {
            $this->addFlash('error', 'Participant à exclure introuvable.');

            return new Response(
                $this->renderView('event/htmx_partials/_exclusions_list.html.twig', [
                    'event' => $participant->getEvent(),
                    'participant' => $participant,
                ])
            );
        }

        $exclusions = $participant->getExclusions() ?? [];

        if (!in_array($excludedParticipantId, $exclusions)) {
            $exclusions[] = $excludedParticipantId;
            $participant->setExclusions($exclusions);
            $this->entityManager->flush();
        }

        return new Response(
            $this->renderView('event/htmx_partials/_exclusions_list.html.twig', [
                'event' => $participant->getEvent(),
                'participant' => $participant,
            ])
        );
    }

    #[Route('/participant/{id}/remove-exclusion/{excludedId}', name: 'app_participant_remove_exclusion', methods: ['DELETE'])]
    public function removeExclusion(int $id, int $excludedId): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant introuvable.');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $exclusions = $participant->getExclusions() ?? [];
        $exclusions = array_filter($exclusions, fn ($e) => $e !== $excludedId);
        $exclusions = array_values($exclusions);

        $participant->setExclusions($exclusions);
        $this->entityManager->flush();

        return new Response(
            $this->renderView('event/htmx_partials/_exclusions_list.html.twig', [
                'event' => $participant->getEvent(),
                'participant' => $participant,
            ])
        );
    }

    #[Route('/participant/{id}/remove-participant', name: 'app_remove_participant', methods: ['DELETE'])]
    public function removeParticipant(int $id): Response
    {
        $participant = $this->participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('error', 'Participant introuvable.');

            return $this->render('partials/_swap_flash_message.html.twig');
        }

        $this->entityManager->remove($participant);
        $this->entityManager->flush();

        return new Response(
            $this->renderView('event/admin/admin_dashboard.html.twig', [
                'event' => $participant->getEvent(),
                'participants' => $participant->getEvent()->getParticipants(),
            ])
        );
    }
}
