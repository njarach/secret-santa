<?php

namespace App\Mailer;

use App\Entity\Event;
use App\Entity\Participant;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class DrawResultMailer extends AbstractEventMailer
{
    /**
     * @throws SyntaxError
     * @throws TransportExceptionInterface
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendDrawResult(Participant $participant, Event $event): void
    {
        $receiver = $participant->getDraw()?->getReceiver();

        if (!$receiver) {
            return;
        }

        $participantAccessUrl = $this->urlGenerator->generate(
            'app_event_access',
            ['id' => $event->getId(), 'token' => $participant->getEventAccessToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = $this->createEmail()
            ->to($participant->getEmail())
            ->subject('🎁'.$participant->getName().' : Résultat du tirage au sort - '.$event->getName())
            ->html(
                $this->twig->render('emails/draw_result.html.twig', [
                    'participant' => $participant,
                    'receiver' => $receiver,
                    'event' => $event,
                    'participantAccessUrl' => $participantAccessUrl,
                ])
            );

        $this->sendMail($email);
    }
}
