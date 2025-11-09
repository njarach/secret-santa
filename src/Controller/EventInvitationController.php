<?php

namespace App\Controller;

use App\Form\InvitationType;
use App\Repository\EventRepository;
use App\Repository\ParticipantRepository;
use App\Security\Voter\AccessVoter;
use App\Services\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventInvitationController extends AbstractController
{
    private EventRepository $eventRepository;
    private ParticipantRepository $participantRepository;
    private EventService $eventService;

    public function __construct(EventRepository $eventRepository, ParticipantRepository $participantRepository, EventService $eventService)
    {
        $this->eventRepository = $eventRepository;
        $this->participantRepository = $participantRepository;
        $this->eventService = $eventService;
    }

    #[Route('/event/{id}/admin/invitation', name: 'app_event_invitation')]
    public function inviteParticipants(Request $request, int $id): Response
    {
        $event = $this->eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted(AccessVoter::ADMIN_ACCESS, $event, "Vous n'avez pas l'autorisation d'inviter des participants.");

        $form = $this->createForm(InvitationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $invitations = $form->getData();
            try {
                $this->eventService->inviteParticipantsToEvent($invitations, $event);
            } catch (\Exception) {
                $this->addFlash('danger', "L'envoi des mails d'invitation a rencontré une erreur technique. Veuillez contacter un administrateur.");
            }

            return $this->redirectToRoute('app_event_admin_dashboard', ['id' => $event->getId()]);
        }

        return $this->render('event_invitation/invitation_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}/{token}/participantId/join-event', name: 'app_event_join_event')]
    public function joinEvent(int $id, string $token, string $participantId): Response
    {
        $event = $this->eventRepository->find($id);
        $participant = $this->participantRepository->find($participantId);
        if (!$event) {
            throw $this->createNotFoundException("L'évènement n'existe pas.");
        }
        if (!$participant) {
            throw $this->createNotFoundException("L'utilisateur n'existe pas.");
        }
        if ($event->getPublicJoinToken() !== $token) {
            throw $this->createAccessDeniedException("Le code d'accès pour rejoindre cet évènement n'est pas valide.");
        }

        try {
            $this->eventService->handleNewParticipantJoining($event, $participant);
        } catch (\Exception) {
            $this->addFlash('error', 'Une erreur technique est survenue, veuillez contacter un administrateur.');

            return $this->render('event_access/participant_verification_failed.html.twig', ['event' => $event]);
        }

        $this->addFlash('success', "Merci d'avoir rejoint cet évènement ! Un mail de bienvenue vous a été envoyé avec vos codes d'accès ;)");

        return $this->redirectToRoute('app_event_access', ['id' => $event->getId(), 'token' => $participant->getEventAccessToken()]);
    }
}
