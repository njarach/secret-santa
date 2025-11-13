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
    public function sendAdminWelcomeMail(Event $event, Participant $adminParticipant): void
    {
        $adminDashboardUrl = $this->urlGenerator->generate(
            'app_event_access',
            ['id' => $event->getId(), 'token' => $event->getAdminAccessToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $participantAccessUrl = $this->urlGenerator->generate(
            'app_event_access',
            ['id' => $event->getId(), 'token' => $adminParticipant->getEventAccessToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = new Email();
        $email
            ->to($event->getAdminEmail())
            ->from('secret-santa@mon-domaine.com')
            ->subject('Bienvenue sur Secret Santa !')
            ->html(
                $this->twig->render('emails/welcome.html.twig', [
                    'event' => $event,
                    'adminDashboardUrl' => $adminDashboardUrl,
                    'participantAccessUrl' => $participantAccessUrl,
                    'adminParticipant' => $adminParticipant,
                ])
            );
        $this->sendMail($email);
    }

    /**
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     */
    public function handleInvitations(Participant $participant, Event $event): void
    {
        $eventJoinToken = $event->getPublicJoinToken();
        $joinEventUrl = $this->urlGenerator->generate(
            'app_event_join_event',
            ['id' => $event->getId(), 'token' => $eventJoinToken, 'participantId' => $participant->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL);
        $newEmail = new Email();
        $newEmail->to($participant->getEmail());
        $newEmail->subject($participant->getName());
        $newEmail->from('secret-santa@domaine.com');
        $newEmail->html(
            $this->twig->render('emails/event_invitation.html.twig', ['joinEventUrl' => $joinEventUrl])
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
        $participantAccessUrl = $this->urlGenerator->generate(
            'app_event_access',
            ['id' => $event->getId(), 'token' => $participant->getEventAccessToken()],
            UrlGeneratorInterface::ABSOLUTE_URL);
        $token = $participant->getEventAccessToken();
        $email = new Email();
        $email
            ->to($participant->getEmail())
            ->from('secret-santa@mon-domaine.com')
            ->subject('Bienvenue sur Secret Santa !')
            ->html(
                $this->twig->render('emails/participant_welcome.html.twig', ['participantAccessUrl' => $participantAccessUrl, 'token' => $token])
            );
        $this->sendMail($email);
    }
}
