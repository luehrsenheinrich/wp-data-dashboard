<?php

namespace App\Repository;

use App\Entity\ThemeTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThemeTag>
 *
 * @method ThemeTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeTag[]    findAll()
 * @method ThemeTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeTagRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ThemeTag::class);
	}

	public function findBySlugs(array $slugs): array
	{
		return $this->createQueryBuilder('t')
			->andWhere('t.slug IN (:slugs)')
			->setParameter('slugs', $slugs)
			->getQuery()
			->getResult();
	}

//    /**
//     * @return ThemeTag[] Returns an array of ThemeTag objects
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

//    public function findOneBySomeField($value): ?ThemeTag
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
