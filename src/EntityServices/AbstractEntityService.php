<?php

namespace App\EntityServices;

use Doctrine\ORM\EntityManagerInterface;

class AbstractEntityService
{
    protected EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    protected function save(object $object, bool $flush = false): void {
        $this->entityManager->persist($object);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

}
