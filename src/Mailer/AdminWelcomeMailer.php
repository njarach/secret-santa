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

final class AdminWelcomeMailer extends AbstractEventMailer
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

        $email = $this->createEmail()
            ->to($event->getAdminEmail())
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
}
