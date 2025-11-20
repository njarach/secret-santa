<?php

namespace App\Mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class AbstractEventMailer
{
    private const FROM_EMAIL = 'Secret Santa <noreply@nicolas-jarach-dev.fr>'; // Change avec ton domaine
    private const FROM_NAME = 'Secret Santa';

    private MailerInterface $mailer;
    protected UrlGeneratorInterface $urlGenerator;
    protected Environment $twig;

    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $urlGenerator, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function sendMail(Email $email): void
    {
        if (empty($email->getFrom())) {
            $email->from(self::FROM_EMAIL);
        }

        $this->mailer->send($email);
    }

    protected function createEmail(): Email
    {
        return (new Email())
            ->from(self::FROM_EMAIL);
    }
}
