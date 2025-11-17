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
    public function verifyEvent(Event $event): Participant
    {
        $event->setStatus(DrawStatus::ACTIVE);
        $event->setVerificationToken(null);
        $event->setVerifiedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $this->save($event, true);

        // Créer le participant admin
        $adminParticipant = new Participant();
        $adminParticipant->setEmail($event->getAdminEmail());
        $adminParticipant->setName('Administrateur');
        $adminParticipant->setEvent($event);
        $adminParticipant->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $adminParticipant->setVerified(true);
        $adminParticipant->setVerifiedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
        $adminParticipant->setEventAccessToken($adminParticipant->generateEventAccessToken());
        $adminParticipant->setAccessTokenExpireAt(
            new \DateTimeImmutable('December 25 +1 month', new \DateTimeZone('UTC'))
        );

        $this->save($event);
        $this->save($adminParticipant, true);

        return $adminParticipant;
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \Exception
     * @throws TransportExceptionInterface
     */
    public function addParticipantsToEvent(mixed $invitations, Event $event): void
    {
        foreach ($invitations as $invitationData) {
            foreach ($invitationData as $participantData) {
                $participantEmail = $participantData['email'];
                $participantName = $participantData['name'];

                $activeParticipant = $this->participantRepository->findAlreadyInvitedParticipant($participantEmail, $participantName, $event->getId());
                if (!$activeParticipant) {
                    $participant = new Participant();
                    $participant->setEmail($participantEmail);
                    $participant->setName($participantName);
                    $participant->setEvent($event);
                    $participant->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
                    $participant->setEventAccessToken($participant->generateEventAccessToken());

                    // TODO : ici peut être imaginer de quoi déclarer qu'un utilisateur n'a pas d'email,  ou alors le mettre lors du tirage (qui envoie els mails)

                    // I think we set this because it's better than not setting it, and eventAccessToken can not be null anyway
                    $participant->setAccessTokenExpireAt(new \DateTimeImmutable('December 25 +1 month', new \DateTimeZone('UTC')));
                    $this->save($participant, true);
                }
            }
        }
    }

    //  TODO : ici ajouter uqelques chose pour gérer l'ajout d'un participant et l'envoidu mail pour lenotifier (ou pas d mail mais mail ça l'atribue son receiver quand même)

    /**
     * @throws \DateMalformedStringException
     */
    public function checkEventVerification(?Event $event, string $token): bool
    {
        $expirationDate = $event->getVerificationSentAt()?->modify('+30 days');
        if (!isset($event) || ($event->getVerificationToken() !== $token) || ($expirationDate && new \DateTimeImmutable() > $expirationDate)) {
            return false;
        }

        return true;
    }
}
