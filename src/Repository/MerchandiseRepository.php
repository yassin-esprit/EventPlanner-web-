<?php

namespace App\Repository;

use App\Entity\Merchandise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Merchandise>
 *
 * @method Merchandise|null find($id, $lockMode = null, $lockVersion = null)
 * @method Merchandise|null findOneBy(array $criteria, array $orderBy = null)
 * @method Merchandise[]    findAll()
 * @method Merchandise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MerchandiseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Merchandise::class);
    }

    public function save(Merchandise $merchandise, bool $flush = false): void
    {
        $this->_em->persist($merchandise);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Merchandise $merchandise, bool $flush = false): void
    {
        $this->_em->remove($merchandise);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findAvailable(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.quantity > 0')
            ->orderBy('m.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySearch(?string $search): array
    {
        $qb = $this->createQueryBuilder('m');

        if ($search) {
            $qb->where('m.name LIKE :search')
               ->orWhere('m.description LIKE :search')
               ->orWhere('m.price LIKE :search')
               ->orWhere('m.quantity LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return $qb->getQuery()->getResult();
    }
}