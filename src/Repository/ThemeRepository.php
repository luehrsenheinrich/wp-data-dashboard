<?php

namespace App\Repository;

use App\ControllerFilter\ThemeFilter;
use App\Entity\Theme;
use App\Entity\ThemeTag;
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

	/**
	 * Find all themes with a given filter.
	 *
	 * @param ThemeFilter $filter The filter.
	 *
	 * @return Paginator The themes.
	 */
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

	/**
	 * Find all themes with a given filter that have a given tag.
	 *
	 * @param ThemeFilter $filter The filter.
	 * @param ThemeTag    $tag    The tag.
	 *
	 * @return Paginator The themes.
	 */
	public function findAllWithFilterByTag(ThemeFilter $filter, ThemeTag $tag)
	{
		$queryBuilder = $this->createQueryBuilder('t');

		// When we do not have a sort, we want to sort by id descending
		if (empty($filter->getSort())) {
			$filter->setSort(['-id']);
		}

		$this->addSort($queryBuilder, $filter->getSort(), 't');

		// Add the filter.
		$this->addFilter($queryBuilder, 'name', $filter->getName(), 't');

		$queryBuilder->join('t.tags', 'tt')
			->andWhere('tt.id = :tagId')
			->setParameter('tagId', $tag->getId());

		return $this->createPaginator($queryBuilder, $filter->getPage(), $filter->getPerPage());
	}

	/**
	 * Get current theme stats.
	 *
	 * @return array
	 */
	public function getCurrentStats(): array
	{
		$stats = $this->createQueryBuilder('t')
			->select('
				SUM(t.activeInstalls) as activeInstalls,
				SUM(t.downloaded) as downloaded,
				COUNT(t.id) as totalThemes,
				COUNT(DISTINCT t.author) as totalAuthors,
				AVG(t.activeInstalls) as averageInstalls')
			->getQuery()
			->getResult();

		return [
			'activeInstalls' => (int) $stats[0]['activeInstalls'],
			'averageInstalls' => (int) $stats[0]['averageInstalls'],
			'downloaded' => (int) $stats[0]['downloaded'],
			'totalThemes' => (int) $stats[0]['totalThemes'],
			'totalAuthors' => (int) $stats[0]['totalAuthors'],
		];
	}

	/**
	 * Get the current average rating for themes that have atleast one rating.
	 *
	 * @return array
	 */
	public function getCurrentAverageRating(): array
	{
		$averageRating = $this->createQueryBuilder('t')
			->select('AVG(t.rating) as averageRating, COUNT(t.id) as totalThemes, SUM(t.numRatings) as totalRatings')
			->where('t.numRatings > 0')
			->getQuery()
			->getOneOrNullResult();

		return [
			'averageRating' => (float) $averageRating['averageRating'],
			'totalThemes' => (int) $averageRating['totalThemes'],
			'totalRatings' => (int) $averageRating['totalRatings'],
		];
	}
}
