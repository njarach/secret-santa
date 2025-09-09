<?php

namespace App\EntityServices;

use App\Entity\Event;
use App\Enum\DrawStatus;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;

class EventService extends AbstractEntityService
{
    /**
     * @param Event $event
     * @return void
     * @throws RandomException
     */
    public function setEventData(Event $event): void
    {
        $createdAt = new \DateTimeImmutable('now');
        $event->setCreatedAt($createdAt);
        $event->setStatus(DrawStatus::DRAFT);

        $adminAccessToken = $this->generateAdminAccessToken();
        $publicJoinToken = $this->generatePublicJoinToken();

        $event->setPublicJoinToken($publicJoinToken);
        $event->setAdminAccessToken($adminAccessToken);
        $this->save($event, true);
    }

    /**
     * @return string
     * @throws RandomException
     */
    private function generateAdminAccessToken(): string
    {
        /* This should NEVER occur, but we check for unicity as good practice */
        do {
            $adminAccessToken = bin2hex(random_bytes(32));
        } while ($this->entityManager->getRepository(Event::class)->findOneBy(['adminAccessToken' => $adminAccessToken]));

        return $adminAccessToken;
    }

    private function generatePublicJoinToken(): string
    {
        /* This should NEVER occur, but we check for unicity as good practice */
        do {
            $publicJoinToken = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while ($this->entityManager->getRepository(Event::class)->findOneBy(['publicJoinToken' => $publicJoinToken]));
        return $publicJoinToken;
    }
}
