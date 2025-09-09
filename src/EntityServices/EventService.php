<?php

namespace App\EntityServices;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventService
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function save(Event $event, bool $flush = false): void {
        $this->entityManager->persist($event);
        if ($flush) {
            $this->entityManager->flush();
        }
    }
    public function findAll(): array {
        return $this->entityManager->getRepository(Event::class)->findAll();
    }

}
