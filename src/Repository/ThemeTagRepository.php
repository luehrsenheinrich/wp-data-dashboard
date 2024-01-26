<?php

namespace App\Repository;

use App\Entity\Theme;
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

	/**
	 * Find theme tags by their slugs.
	 *
	 * @param array $slugs The slugs of the theme tags to find.
	 *
	 * @return array The theme tags that were found.
	 */
	public function findBySlugs(array $slugs): array
	{
		$tagEntities = $this->createQueryBuilder('t')
			->andWhere('t.slug IN (:slugs)')
			->setParameter('slugs', $slugs)
			->getQuery()
			->getResult();

		$tags = [];
		foreach ($tagEntities as $tagEntity) {
			$tags[$tagEntity->getSlug()] = $tagEntity;
		}

		return $tags;
	}

	/**
	 * Find all theme tags
	 *
	 * @return array The theme tags that were found.
	 */
	public function findAll(): array
	{
		$builder = $this->createQueryBuilder('t');

		// Find the number of themes that are tagged with this theme tag. We need to look from the theme side,
		// as the tag side does not have a relation to the theme.
		$builder->select('t, COUNT(theme.id) AS themeCount')
			->leftJoin(Theme::class, 'theme', 'WITH', 't MEMBER OF theme.tags')
			->orderBy('themeCount', 'DESC')
			->having('COUNT(theme.id) > 1')
			->groupBy('t.id');

		$tagEntities = $builder->getQuery()
						->getResult();

		$tags = [];
		foreach ($tagEntities as $tagEntity) {
			$e = $tagEntity[0];
			$e->setThemeCount($tagEntity['themeCount']);

			$tags[] = $e;
		}

		return $tags;
	}
}
