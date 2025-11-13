<?php

namespace App\Controller;

use App\Mailer\EventParticipantMailer;
use App\Mailer\EventVerificationMailer;
use App\Repository\EventRepository;
use App\Services\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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
     * @throws \Exception
     */
    #[Route('/event/verify/{id}/{token}', name: 'app_event_verify')]
    public function verify(int $id, string $token): Response
    {
        $event = $this->eventRepository->find($id);
        try {
            if (!$this->eventService->checkEventVerification($event, $token)) {
                return $this->render('event_verification/event_verification_failed.html.twig');
            }
        } catch (\DateMalformedStringException) {
            $this->addFlash('error', "Une erreur technique s'est produite, veuillez contacter un administrateur.");
            return $this->render('event_verification/event_verification_failed.html.twig');
        }

        try {
            $adminParticipant = $this->eventService->verifyEvent($event);
            $this->eventParticipantMailer->sendAdminWelcomeMail($event, $adminParticipant);
        } catch (LoaderError|RuntimeError|SyntaxError|TransportExceptionInterface $exception) {
            throw new \Exception($exception->getMessage());
        }

        $this->addFlash('success', "Bienvenue sur Secret Santa ! Vous avez reçu un mail avec vos codes d'accès ;)");

        return $this->redirectToRoute('app_event_access', ['id' => $event->getId(), 'token' => $event->getAdminAccessToken()]);
    }

    #[Route('/event/resend/{id}', name: 'app_event_verification_resend_email')]
    public function resend(int $id, EventVerificationMailer $eventVerificationMailer): Response
    {
        $event = $this->eventRepository->find($id);
        try {
            $eventVerificationMailer->sendEventVerificationEmail($event);
            $this->addFlash('success', 'Email de vérification renvoyé avec succès.');
        } catch (TransportExceptionInterface|LoaderError|RuntimeError|SyntaxError) {
            $this->addFlash('danger', 'Le renvoi du mail a échoué.');
        }

        return $this->render('event/pending_verification.html.twig', [
            'event' => $event,
        ]);
    }
}
