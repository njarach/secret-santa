<?php

namespace App\Services;

use App\Entity\Event;
use App\Entity\Participant;
use App\Enum\DrawStatus;
use App\Mailer\EventParticipantMailer;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EventService extends AbstractEntityService
{
    private ParticipantRepository $participantRepository;
    private EventParticipantMailer $eventParticipantMailer;

    public function __construct(EntityManagerInterface $entityManager, ParticipantRepository $participantRepository, EventParticipantMailer $eventParticipantMailer)
    {
        parent::__construct($entityManager);
        $this->participantRepository = $participantRepository;
        $this->eventParticipantMailer = $eventParticipantMailer;
    }

    /**
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function setEventData(Event $event): void
    {
        $createdAt = new \DateTimeImmutable('now');
        $event->setCreatedAt($createdAt);
        $event->setStatus(DrawStatus::DRAFT);

        $adminAccessToken = $event->generateAdminAccessToken();
        $publicJoinToken = $event->generatePublicJoinToken();
        $verificationToken = $event->generateVerificationToken();

        $event->setPublicJoinToken($publicJoinToken);
        $event->setAdminAccessToken($adminAccessToken);
        $event->setVerificationToken($verificationToken);
        $event->setPublicAccessTokenExpireAt(new \DateTimeImmutable('+30 days', new \DateTimeZone('UTC')));
        $event->setVerificationSentAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));

        $this->save($event, true);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function verifyEvent(Event $event): void
    {
        $event->setStatus(DrawStatus::ACTIVE);
        $event->setVerificationToken(null);
        $event->setVerifiedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $this->save($event, true);
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    public function inviteParticipantsToEvent(mixed $invitations, Event $event): void
    {
        foreach ($invitations as $invitationData) {
            foreach ($invitationData as $participant) {
                $participantEmail = $participant['email'];
                $participantName = $participant['name'];

                $activeParticipant = $this->participantRepository->findAlreadyInvitedParticipant($participantEmail, $participantName, $event->getId());
                if (!$activeParticipant) {
                    $participant = new Participant();
                    $participant->setEmail($participantEmail);
                    $participant->setName($participantName);
                    $participant->setEvent($event);
                    $participant->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
                    $participant->setVerified(false);
                    $this->save($participant, true);
                }

                $eventJoinToken = $event->getPublicJoinToken();
                if (!$eventJoinToken || $event->getPublicAccessTokenExpireAt() < new \DateTimeImmutable('now')) {
                    throw new \Exception("Ce token n'est pas valide, veuillez contacter un administrateur pour rejoindre l'évènement.");
                }

                $this->eventParticipantMailer->handleInvitations($participant, $event);

                // cette méthode doit : trouver les noms et les mails donnés par l'admin lors du remplissage du formulaire, puis
                /* créer et envoyer un mail à chacun des participants en incluant dans le mail l'url avec le token d'accès
                public. ce token peut être utilisé pour créer un compte 'participant' sur l'évènement. Ce token sert avant tout
                à vérifier la participation des participants invités, il s'appelle public join mais pourrait bien s'appeler
                public participant verification token (il a aussi une date d'expiration). Le participant est bien créé dans la base
                mais il n'a pas encore été vérifié. TODO : peut être ajouter une propriété 'verifiedAt' pour les participants
                ou encore joinedAt. Une fois ajouté et ensuite vérifié (son nom et son email sont renseignés par l'admin lors
                de l'ajout mais le participant peut les modifier) il reçoit un mail de bienvenue avec un lien pour accéder à son dashboard
                et son token + nom de l'event. Son token a TODO : ajouter une date d expiration et possibilité pour l'admin de renvoyer un token, voir pour ajouter cloudflare au projet.
                */
            }
        }
    }
}
