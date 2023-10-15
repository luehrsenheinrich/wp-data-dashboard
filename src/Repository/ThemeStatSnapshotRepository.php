<?php

namespace App\Repository;

use App\Entity\ThemeStatSnapshot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThemeStatSnapshot>
 *
 * @method ThemeStatSnapshot|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeStatSnapshot|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeStatSnapshot[]    findAll()
 * @method ThemeStatSnapshot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeStatSnapshotRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ThemeStatSnapshot::class);
	}

	/**
	 * Get the total number of theme stat snapshots.
	 *
	 * @return int
	 */
	public function getTotalCount(): int
	{
		return $this->createQueryBuilder('t')
			->select('COUNT(t.id)')
			->getQuery()
			->getSingleScalarResult();
	}

//    /**
//     * @return ThemeStatSnapshot[] Returns an array of ThemeStatSnapshot objects
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

//    public function findOneBySomeField($value): ?ThemeStatSnapshot
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
