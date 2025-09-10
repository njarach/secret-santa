<?php

namespace App\Mailer;

use App\Entity\Event;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EventVerificationMailer extends AbstractEventMailer
{
    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendEventVerificationEmail(Event $event): void
    {
        $verificationLink = $this->urlGenerator->generate('app_event_access',
            ['id'=>$event->getId(), 'token'=>$event->getVerificationToken()],
            UrlGeneratorInterface::ABSOLUTE_URL);

        $email = new Email();
        $email
            ->to($event->getAdminEmail())
            ->from('no-reply@mon-domaine.com')
            ->subject('Veuillez confirmer votre Secret Santa')
            ->html(
                $this->twig->render(
                    'emails/event_verification.html.twig', ['url' => $verificationLink, 'event' => $event]
                )
            );
        $this->sendMail($email);
    }
}
