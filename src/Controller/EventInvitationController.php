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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/event/{id}/admin/add-participants', name: 'app_event_add_particpants')]
    public function addParticipants(Request $request, int $id): Response
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
                $this->eventService->addParticipantsToEvent($invitations, $event);
            } catch (\Exception) {
                $this->addFlash('danger', "L'envoi des mails d'invitation a rencontré une erreur technique. Veuillez contacter un administrateur.");
            }

            return $this->redirectToRoute('app_event_admin_dashboard', ['id' => $event->getId()]);
        }

        return $this->render('event_invitation/invitation_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
