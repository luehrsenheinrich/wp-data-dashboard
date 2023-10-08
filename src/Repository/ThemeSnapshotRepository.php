<?php

namespace App\Repository;

use App\Entity\ThemeSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThemeSnapshot>
 *
 * @method ThemeSnapshot|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeSnapshot|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeSnapshot[]    findAll()
 * @method ThemeSnapshot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeSnapshotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemeSnapshot::class);
    }

//    /**
//     * @return ThemeSnapshot[] Returns an array of ThemeSnapshot objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ThemeSnapshot
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
