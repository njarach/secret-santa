<?php

namespace App\Services;

use App\Entity\Draw;
use App\Entity\Event;
use App\Entity\Participant;
use App\Enum\DrawStatus;
use Doctrine\ORM\EntityManagerInterface;

class DrawService extends AbstractEntityService
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * Effectue le tirage au sort en respectant les exclusions.
     *
     * @throws \Exception
     */
    public function performDraw(Event $event): void
    {
        if (DrawStatus::DRAWN === $event->getStatus()) {
            throw new \Exception('Le tirage a déjà été effectué pour cet événement.');
        }

        $participants = $event->getParticipants()->toArray();

        if (count($participants) < 3) {
            throw new \Exception('Il faut au moins 3 participants vérifiés pour effectuer un tirage.');
        }

        $exclusions = $this->buildExclusionsMap($participants);

        $assignments = $this->generateValidAssignments($participants, $exclusions);

        if (null === $assignments) {
            throw new \Exception('Impossible de réaliser le tirage avec les exclusions actuelles. Veuillez modifier les exclusions.');
        }

        foreach ($assignments as $giverId => $receiverId) {
            $draw = new Draw();
            $draw->setEvent($event);
            $draw->setGiver($this->findParticipantById($participants, $giverId));
            $draw->setReceiver($this->findParticipantById($participants, $receiverId));
            $draw->setCreatedAt(new \DateTimeImmutable());

            $this->save($draw);
        }

        $event->setStatus(DrawStatus::DRAWN);
        $event->setDrawnAt(new \DateTimeImmutable());
        $this->save($event, true);
    }

    private function buildExclusionsMap(array $participants): array
    {
        $exclusions = [];

        foreach ($participants as $participant) {
            $exclusions[$participant->getId()] = $participant->getExclusions() ?? [];
            $exclusions[$participant->getId()][] = $participant->getId();
        }

        return $exclusions;
    }

    /**
     * Génère des assignations valides avec backtracking.
     */
    private function generateValidAssignments(array $participants, array $exclusions, int $maxAttempts = 100): ?array
    {
        $ids = array_map(fn ($p) => $p->getId(), $participants);

        for ($attempt = 0; $attempt < $maxAttempts; ++$attempt) {
            $receivers = $ids;
            shuffle($receivers);

            if ($this->isValidAssignment($ids, $receivers, $exclusions)) {
                return array_combine($ids, $receivers);
            }
        }

        return $this->backtrackAssignment($ids, [], $exclusions);
    }

    private function backtrackAssignment(array $remainingGivers, array $currentAssignment, array $exclusions): ?array
    {
        if (empty($remainingGivers)) {
            return $currentAssignment;
        }

        $giver = array_shift($remainingGivers);
        $allIds = array_keys($exclusions);

        foreach ($allIds as $receiver) {
            if (in_array($receiver, $currentAssignment)
                || in_array($receiver, $exclusions[$giver] ?? [])) {
                continue;
            }

            if (empty($remainingGivers) && isset($currentAssignment[$receiver]) && $currentAssignment[$receiver] === array_key_first($currentAssignment)) {
                if (1 === count($currentAssignment)) {
                    continue;
                }
            }

            $newAssignment = $currentAssignment;
            $newAssignment[$giver] = $receiver;

            $result = $this->backtrackAssignment($remainingGivers, $newAssignment, $exclusions);
            if (null !== $result) {
                return $result;
            }
        }

        return null;
    }

    private function isValidAssignment(array $givers, array $receivers, array $exclusions): bool
    {
        foreach ($givers as $index => $giver) {
            $receiver = $receivers[$index];

            if (in_array($receiver, $exclusions[$giver] ?? [])) {
                return false;
            }
        }

        return true;
    }

    private function findParticipantById(array $participants, int $id): ?Participant
    {
        foreach ($participants as $participant) {
            if ($participant->getId() === $id) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * Récupère tous les tirages pour un événement (pour l'admin qui révèle).
     */
    public function getDrawsForEvent(Event $event): array
    {
        return $event->getDraws()->toArray();
    }
}
