<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Keeps a "position" integer column contiguous and conflict-free when an
 * admin CRUD creates or moves an entity, so editors never have to manually
 * renumber every other row by hand.
 */
final class PositionReorderer
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Call before inserting a new entity: shifts every existing row whose
     * position is >= the new row's position up by one, freeing that slot.
     *
     * $scopeCondition/$scopeParameters let the shift stay within a subset of
     * rows (e.g. "e.project = :project" for images grouped by project)
     * instead of the whole table.
     *
     * @param array<string, mixed> $scopeParameters
     */
    public function makeRoomForInsert(string $entityClass, int $position, string $scopeCondition = '1 = 1', array $scopeParameters = []): void
    {
        $query = $this->entityManager->createQuery(
            "UPDATE $entityClass e SET e.position = e.position + 1 WHERE e.position >= :position AND $scopeCondition"
        )->setParameter('position', $position);

        foreach ($scopeParameters as $name => $value) {
            $query->setParameter($name, $value);
        }

        $query->execute();
    }

    /**
     * Call before updating an existing entity whose position may have
     * changed: shifts the rows between the old and new position by one to
     * close the gap / open a slot, so no two rows end up sharing a position.
     *
     * @param array<string, mixed> $scopeParameters
     */
    public function moveExisting(string $entityClass, int $id, int $oldPosition, int $newPosition, string $scopeCondition = '1 = 1', array $scopeParameters = []): void
    {
        if ($newPosition === $oldPosition) {
            return;
        }

        if ($newPosition < $oldPosition) {
            $dql = "UPDATE $entityClass e SET e.position = e.position + 1 WHERE e.position >= :new AND e.position < :old AND e.id != :id AND $scopeCondition";
        } else {
            $dql = "UPDATE $entityClass e SET e.position = e.position - 1 WHERE e.position > :old AND e.position <= :new AND e.id != :id AND $scopeCondition";
        }

        $query = $this->entityManager->createQuery($dql)
            ->setParameter('new', $newPosition)
            ->setParameter('old', $oldPosition)
            ->setParameter('id', $id);

        foreach ($scopeParameters as $name => $value) {
            $query->setParameter($name, $value);
        }

        $query->execute();
    }
}
