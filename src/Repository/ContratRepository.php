<?php

namespace App\Repository;

use App\Entity\Contrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contrat>
 */
class ContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contrat::class);
    }
    public function findPaginated(int $page, int $limit): array
{
    $offset = ($page - 1) * $limit;

    return $this->createQueryBuilder('c')
        ->setFirstResult($offset)
        ->setMaxResults($limit)
        ->orderBy('c.id_contrat', 'DESC')
        ->getQuery()
        ->getResult();
}
public function countContratsBySponsor(): array
{
    return $this->createQueryBuilder('c')
        ->select('s.nom AS sponsor', 'COUNT(c.id_contrat) AS nbContrats')
        ->join('c.sponsor', 's')
        ->groupBy('s.id_sponsor')
        ->getQuery()
        ->getResult();
}

public function countActiveContrats(): int
{
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id_contrat)')
        ->where('c.statut = :statut')
        ->setParameter('statut', 'Actif')
        ->getQuery()
        ->getSingleScalarResult();
}

public function countInactiveContrats(): int
{
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id_contrat)')
        ->where('c.statut = :statut')
        ->setParameter('statut', 'Inactif')
        ->getQuery()
        ->getSingleScalarResult();
}

public function getTotalMontant(): float
{
    return $this->createQueryBuilder('c')
        ->select('SUM(c.montant)')
        ->getQuery()
        ->getSingleScalarResult() ?? 0;
}



    //    /**
    //     * @return Contrat[] Returns an array of Contrat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Contrat
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
