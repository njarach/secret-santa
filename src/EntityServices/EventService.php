<?php

namespace App\EntityServices;

use App\Entity\Event;
use App\Enum\DrawStatus;
use Random\RandomException;

class EventService extends AbstractEntityService
{
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
}
