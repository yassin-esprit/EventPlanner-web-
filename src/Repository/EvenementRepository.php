<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    //    /**
    //     * @return Evenement[] Returns an array of Evenement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evenement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function countEventsByType(): array
{
    return $this->createQueryBuilder('e')
        ->select('e.type as eventType, COUNT(e.id) as count')
        ->groupBy('e.type')
        ->getQuery()
        ->getResult();
}

public function countUpcomingEvents(): int
{
    return $this->createQueryBuilder('e')
        ->select('COUNT(e.id)')
        ->where('e.date >= :today')
        ->setParameter('today', new \DateTime())
        ->getQuery()
        ->getSingleScalarResult();
}

public function getParticipationStats(): array
{
    return $this->createQueryBuilder('e')
        ->select('e.id, e.titre, COUNT(p.userId) as participantCount')
        ->leftJoin('e.users', 'p')
        ->groupBy('e.id')
        ->orderBy('participantCount', 'DESC')
        ->getQuery()
        ->getResult();
}
// src/Repository/EvenementRepository.php

public function findWithSearchAndSort(
    ?string $search = null,
    string $sort = 'date',
    string $direction = 'asc'
): array {
    $qb = $this->createQueryBuilder('e');

    if ($search) {
        $qb->andWhere('e.titre LIKE :search OR e.description LIKE :search OR e.type LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    // Add sorting
    switch ($sort) {
        case 'title':
            $qb->orderBy('e.titre', $direction);
            break;
        case 'type':
            $qb->orderBy('e.type', $direction);
            break;
        case 'date':
        default:
            $qb->orderBy('e.date', $direction);
            break;
    }

    return $qb->getQuery()->getResult();
}
}
