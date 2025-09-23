<?php

namespace App\Mailer;

use App\Entity\Event;
use App\Entity\Participant;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class EventParticipantMailer extends AbstractEventMailer
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws TransportExceptionInterface
     */
    public function sendAdminWelcomeMail(Event $event): void
    {
        $adminParticipantAccessUrl = $this->urlGenerator->generate('app_event_admin_dashboard', ['id' => $event->getId(), 'token' => $event->getAdminAccessToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = new Email();
        $email
        ->to($event->getAdminEmail())
        ->from('secret-santa@mon-domaine.com')
        ->subject('Bienvenue sur Secret Santa !')
        ->html(
            $this->twig->render('emails/welcome.html.twig', ['event' => $event, 'adminParticipantAccessUrl' => $adminParticipantAccessUrl])
        );
        $this->sendMail($email);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array<string> $participant
     */
    public function handleInvitations(array $participant, Event $event): void
    {
        $eventJoinToken = $event->getPublicJoinToken();
        $newEmail = new Email();
        $newEmail->to($participant['email']);
        $newEmail->subject($participant['name']);
        $newEmail->from('secret-santa@domaine.com');
        $newEmail->html(
            $this->twig->render('emails/event_invitation.html.twig', ['eventJoinToken' => $eventJoinToken, 'event' => $event, 'participant' => $participant])
        );
        $this->sendMail($newEmail);
    }

    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendParticipantWelcomeMail(Participant $participant, Event $event): void
    {
        $participantAccessUrl = $this->urlGenerator->generate('app_event_access', ['id' => $event->getId(), 'token' => $participant->getEventAccessToken()], UrlGeneratorInterface::ABSOLUTE_URL);
        $email = new Email();
        $email
            ->to($participant->getEmail())
            ->from('secret-santa@mon-domaine.com')
            ->subject('Bienvenue sur Secret Santa !')
            ->html(
                $this->twig->render('emails/participant_welcome.html.twig', ['event' => $event, 'participantAccessUrl' => $participantAccessUrl])
            );
        $this->sendMail($email);
    }
}
