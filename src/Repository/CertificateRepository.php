<?php

namespace App\Repository;

use App\Entity\Certificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Certificate>
 */
class CertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Certificate::class);
    }

    /**
     * @return Certificate[]
     */
    public function findPublishedOrderedByPosition(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.published = true')
            ->orderBy('c.position', 'ASC')
            ->addOrderBy('c.issueDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
