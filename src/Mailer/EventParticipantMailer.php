<?php

namespace App\Mailer;

use App\Entity\Event;
use Symfony\Component\Mime\Email;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class EventParticipantMailer extends AbstractEventMailer
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendAdminWelcomeMail(Event $event): void
    {
        $email = new Email();
        $email
        ->to($event->getAdminEmail())
        ->from('secret-santa@mon-domaine.com')
        ->subject('Bienvenue sur Secret Santa !')
        ->html(
            $this->twig->render('emails/welcome.html.twig', ['event' => $event])
        );
    }
}
