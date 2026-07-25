<?php

namespace App\Repository;

use App\Entity\SkillCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SkillCategory>
 */
class SkillCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SkillCategory::class);
    }

    /**
     * @return SkillCategory[]
     */
    public function findPublishedOrderedByPosition(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.published = true')
            ->orderBy('c.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
