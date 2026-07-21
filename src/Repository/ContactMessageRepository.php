<?php

namespace App\Repository;

use App\Entity\ContactMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactMessage>
 */
class ContactMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactMessage::class);
    }

    /**
     * Nombre de messages envoyés depuis une IP sur les dernières minutes.
     * Sert de garde-fou anti-flood en complément du honeypot.
     */
    public function countRecentFromIp(string $ipAddress, int $minutes): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.ipAddress = :ip')
            ->andWhere('m.createdAt > :since')
            ->setParameter('ip', $ipAddress)
            ->setParameter('since', new \DateTimeImmutable(sprintf('-%d minutes', $minutes)))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
