<?php

namespace App\Controller;

use App\Entity\Event;
use App\EntityServices\EventService;
use App\Mailer\EventParticipantMailer;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class EventVerificationController extends AbstractController
{
    private EventService $eventService;
    private EventRepository $eventRepository;
    private EventParticipantMailer $eventParticipantMailer;
    public function __construct(EventService $eventService, EventRepository $eventRepository, EventParticipantMailer $eventParticipantMailer)
    {
        $this->eventService = $eventService;
        $this->eventRepository = $eventRepository;
        $this->eventParticipantMailer = $eventParticipantMailer;
    }

    /**
     * @throws \DateMalformedStringException
     */
    #[Route('/event/verify/{id}/{verification_token}', name: 'app_event_verify')]
    public function verify(int $id, string $verificationToken): Response
    {
        $event = $this->eventRepository->find($id);

        if (!isset($event) || ($event->getVerificationToken() !== $verificationToken)) {
            return $this->render('event_verification/event_verification_failed.html.twig');
        }

        $this->eventService->verifyEvent($event);

        try {
            $this->eventParticipantMailer->sendAdminWelcomeMail($event);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('app_new_event');
        }

        return $this->redirectToRoute('app_event_access', ['id' => $event->getId(), 'token' => $event->getAdminAccessToken()]);
    }

    #[Route('/event/resend/{id}/{verification_token}', name: 'app_event_verification_resend_email')]
    public function resend(int $id, string $verificationToken): Response
    {
        $event = $this->eventRepository->find($id);
        return new Response('', Response::HTTP_OK);
    }
}
