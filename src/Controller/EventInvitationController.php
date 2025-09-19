<?php

namespace App\Controller;

use App\Form\InvitationType;
use App\Mailer\EventParticipantMailer;
use App\Repository\EventRepository;
use App\Security\Voter\AccessVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventInvitationController extends AbstractController
{
    private EventRepository $eventRepository;
    private EventParticipantMailer $eventParticipantMailer;

    public function __construct(EventRepository $eventRepository, EventParticipantMailer $eventParticipantMailer)
    {
        $this->eventRepository = $eventRepository;
        $this->eventParticipantMailer = $eventParticipantMailer;
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
            $this->eventParticipantMailer->handleInvitations($invitations, $event);
            return $this->redirectToRoute('app_event_admin_dashboard', ['id' => $event->getId()]);
        }

        return $this->render('event_invitation/invitation_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/event/{id}/{token}/join-event', name: 'app_event_join_event')]
    public function joinEvent(int $id, string $token): Response
    {
        return new Response('Join event page, accessed with the invite sent by the event creator. New participant fills form and submitting confirms participation, redirected to user dashboard.', Response::HTTP_OK);
    }
}
