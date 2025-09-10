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

        $adminAccessToken = $event->generateAdminAccessToken();
        $publicJoinToken = $event->generatePublicJoinToken();

        $event->setPublicJoinToken($publicJoinToken);
        $event->setAdminAccessToken($adminAccessToken);
        $this->save($event, true);
    }
}
