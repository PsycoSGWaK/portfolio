<?php

namespace App\Repository;

use App\Entity\Education;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Education>
 */
class EducationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Education::class);
    }

    /**
     * @return Education[]
     */
    public function findPublishedOrderedByPosition(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.published = true')
            ->orderBy('e.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
