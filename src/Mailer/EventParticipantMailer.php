<?php

namespace App\Mailer;

use App\Entity\Event;
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

    public function sendParticipantInvitationMail(Event $event): void
    {

    }
}
