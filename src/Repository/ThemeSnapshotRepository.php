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

	/**
	 * Get the total number of theme snapshots.
	 *
	 * @return int
	 */
	public function getTotalSnapshots(): int
	{
		return $this->createQueryBuilder('t')
			->select('COUNT(t.id)')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Get the total number of themes.
	 * To get the total number of themes, we need to group by the slug.
	 *
	 * @return int
	 */
	public function getTotalThemes(): int
	{
		return $this->createQueryBuilder('t')
			->select('COUNT(DISTINCT t.slug)')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Get the newest theme snapshot for a given theme slug.
	 *
	 * @param string $slug
	 *
	 * @return ThemeSnapshot|null
	 */
	public function getNewestThemeSnapshot(string $slug): ?ThemeSnapshot
	{
		return $this->createQueryBuilder('t')
			->where('t.slug = :slug')
			->setParameter('slug', $slug)
			->orderBy('t.createdAt', 'DESC')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Find the newest theme snapshots for given theme slugs.
	 */
	public function findNewestThemeSnapshotBySlugs(array $slugs): array
	{
		return $this->createQueryBuilder('t')
			->where('t.slug IN (:slugs)')
			->setParameter('slugs', $slugs)
			->orderBy('t.createdAt', 'DESC')
			->groupBy('t.slug')
			->getQuery()
			->getResult();
	}
}
