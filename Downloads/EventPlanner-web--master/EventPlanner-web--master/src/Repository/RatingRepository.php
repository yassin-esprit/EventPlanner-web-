<?php

namespace App\Repository;

use App\Entity\Rating;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    //    /**
    //     * @return Rating[] Returns an array of Rating objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Rating
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function getAverageRatings(): array
{
    return $this->createQueryBuilder('r')
        ->select('e.id, e.titre, AVG(r.rating) as avgRating')
        ->join('r.evenement', 'e')
        ->groupBy('e.id')
        ->orderBy('avgRating', 'DESC')
        ->getQuery()
        ->getResult();
}

public function getRatingDistribution(): array
{
    return $this->createQueryBuilder('r')
        ->select('r.rating, COUNT(r.id) as count')
        ->groupBy('r.rating')
        ->orderBy('r.rating', 'ASC')
        ->getQuery()
        ->getResult();
}
}
