<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    //    /**
    //     * @return Participant[] Returns an array of Participant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Participant
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findOneByEventAndToken(Event $event, string $token): ?Participant
    {
        return $this->createQueryBuilder('p')
                ->where('p.event = :event')
                ->andWhere('p.eventAccessToken = :token')
                ->setParameter('event', $event)
                ->setParameter('token', $token)
                ->getQuery()
                ->getOneOrNullResult()
        ;
    }

    public function findAlreadyInvitedParticipant(mixed $participantEmail, mixed $participantName, ?int $getId): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->where('p.email = :email')
            ->andWhere('p.name = :name')
            ->andWhere('p.event.id = :eventId')
            ->setParameter('email', $participantEmail)
            ->setParameter('name', $participantName)
            ->setParameter('eventId', $getId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
