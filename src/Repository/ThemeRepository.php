<?php

namespace App\Repository;

use App\ControllerFilter\ThemeFilter;
use App\Entity\Theme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Traits\RepositoryFilterHelperTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Theme>
 *
 * @method Theme|null find($id, $lockMode = null, $lockVersion = null)
 * @method Theme|null findOneBy(array $criteria, array $orderBy = null)
 * @method Theme[]    findAll()
 * @method Theme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeRepository extends ServiceEntityRepository
{
	use RepositoryFilterHelperTrait;

	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Theme::class);
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
	 * Get the newest theme for a given theme slug.
	 *
	 * @param string $slug
	 *
	 * @return Theme|null
	 */
	public function getBySlug(string $slug): ?Theme
	{
		return $this->createQueryBuilder('t')
			->where('t.slug = :slug')
			->setParameter('slug', $slug)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Find the newest themes for given theme slugs.
	 * Returns an array of Theme entities indexed by their slug.
	 *
	 * @param array $themes An array of theme slugs.
	 *
	 * @return Theme[]
	 */
	public function findThemesBySlugs(array $slugs): array
	{
		$themes = $this->createQueryBuilder('t')
			->where('t.slug IN (:slugs)')
			->setParameter('slugs', $slugs)
			->getQuery()
			->getResult();

		$themesBySlug = array_combine(
			array_map(
				static fn (Theme $theme): string => $theme->getSlug(),
				$themes
			),
			$themes
		);

		return $themesBySlug;
	}

	public function findAllWithFilter(ThemeFilter $filter): Paginator
	{
		$queryBuilder = $this->createQueryBuilder('t');

		// When we do not have a sort, we want to sort by id descending
		if (empty($filter->getSort())) {
			$filter->setSort(['-id']);
		}

		$this->addSort($queryBuilder, $filter->getSort(), 't');

		// Add the filter.
		$this->addFilter($queryBuilder, 'name', $filter->getName(), 't');

		return $this->createPaginator($queryBuilder, $filter->getPage(), $filter->getPerPage());
	}
}
