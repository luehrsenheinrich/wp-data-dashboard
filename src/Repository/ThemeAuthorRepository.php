<?php

namespace App\Repository;

use App\Entity\ThemeAuthor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ThemeAuthor>
 *
 * @method ThemeAuthor|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeAuthor|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeAuthor[]    findAll()
 * @method ThemeAuthor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeAuthorRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, ThemeAuthor::class);
	}

	/**
	 * Find one theme author by their user_nicename.
	 *
	 * @param string $userNicename The user_nicename of the theme author.
	 *
	 * @return ThemeAuthor|null
	 */
	public function findOneByUserNicename(string $userNicename)
	{
		return $this->createQueryBuilder('ta')
			->where('ta.userNicename = :userNicename')
			->setParameter('userNicename', $userNicename)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * Find all theme authors by their user_nicename.
	 *
	 * @param string[] $userNicenames An array of user_nicenames.
	 *
	 * @return ThemeAuthor[] An array of ThemeAuthor entities, indexed by their user_nicename.
	 */
	public function findByUserNicenames(array $userNicenames): array
	{
		$authors = $this->createQueryBuilder('ta')
			->where('ta.userNicename IN (:userNicenames)')
			->setParameter('userNicenames', $userNicenames)
			->getQuery()
			->getResult();

		$indexedAuthors = [];
		foreach ($authors as $author) {
			$indexedAuthors[$author->getUserNicename()] = $author;
		}

		return $indexedAuthors;
	}
}
