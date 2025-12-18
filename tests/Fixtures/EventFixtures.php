<?php

namespace App\Tests\Fixtures;

use App\Entity\Event;
use App\Entity\Participant;
use App\Enum\DrawStatus;
use Doctrine\ORM\EntityManagerInterface;

class EventFixtures
{
    public static function createActiveEvent(EntityManagerInterface $em, string $adminEmail = 'admin@test.com'): Event
    {
        $event = new Event();
        $event->setName('Test Secret Santa');
        $event->setDescription('Event de test');
        $event->setBudget(30.0);
        $event->setAdminEmail($adminEmail);
        $event->setStatus(DrawStatus::ACTIVE);
        $event->setCreatedAt(new \DateTimeImmutable());
        $event->setVerifiedAt(new \DateTimeImmutable());
        $event->setVerificationSentAt(new \DateTimeImmutable());
        $event->setAdminAccessToken(bin2hex(random_bytes(32)));

        $em->persist($event);
        $em->flush();

        return $event;
    }

    public static function createDraftEvent(EntityManagerInterface $em, string $adminEmail = 'admin@test.com'): Event
    {
        $event = new Event();
        $event->setName('Draft Secret Santa');
        $event->setAdminEmail($adminEmail);
        $event->setStatus(DrawStatus::DRAFT);
        $event->setCreatedAt(new \DateTimeImmutable());
        $event->setVerificationSentAt(new \DateTimeImmutable());
        $event->setVerificationToken(bin2hex(random_bytes(32)));
        $event->setAdminAccessToken(bin2hex(random_bytes(32)));

        $em->persist($event);
        $em->flush();

        return $event;
    }

    public static function createParticipant(EntityManagerInterface $em, Event $event, string $name, string $email): Participant
    {
        $participant = new Participant();
        $participant->setName($name);
        $participant->setEmail($email);
        $participant->setEvent($event);
        $participant->setCreatedAt(new \DateTimeImmutable());
        $participant->setEventAccessToken(bin2hex(random_bytes(32)));
        $participant->setAccessTokenExpireAt(new \DateTimeImmutable('+1 month'));

        $em->persist($participant);
        $em->flush();

        return $participant;
    }

    public static function createMultipleParticipants(EntityManagerInterface $em, Event $event, int $count = 3): array
    {
        $participants = [];
        for ($i = 1; $i <= $count; ++$i) {
            $participants[] = self::createParticipant($em, $event, "User $i", "user$i@test.com");
        }

        return $participants;
    }
}
