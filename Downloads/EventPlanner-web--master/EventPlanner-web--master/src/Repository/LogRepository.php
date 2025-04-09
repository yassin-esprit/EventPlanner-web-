<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function findRecentLogs(int $limit = 100)
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLogsByUser(int $userId, int $limit = 50)
    {
        return $this->createQueryBuilder('l')
            ->where('l.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLogsByAction(string $action, int $limit = 50)
    {
        return $this->createQueryBuilder('l')
            ->where('l.action = :action')
            ->setParameter('action', $action)
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLogsByLevel(string $level, int $limit = 50)
    {
        return $this->createQueryBuilder('l')
            ->where('l.level = :level')
            ->setParameter('level', $level)
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findFailedLoginAttempts(int $limit = 50)
    {
        return $this->createQueryBuilder('l')
            ->where('l.action = :action')
            ->andWhere('l.level = :level')
            ->setParameter('action', 'LOGIN_FAILED')
            ->setParameter('level', 'WARNING')
            ->orderBy('l.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
} 