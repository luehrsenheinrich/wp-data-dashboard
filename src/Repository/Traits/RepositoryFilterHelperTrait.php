<?php

declare(strict_types=1);

namespace App\Repository\Traits;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Extends the ServiceEntityRepository and injects some custom helper methods.
 */
trait RepositoryFilterHelperTrait
{
	/**
	 * Add the desired sort to the query builder.
	 * The sort is an array of strings, each string is a field name prefixed with a + or - to indicate the sort direction.
	 *
	 * @param QueryBuilder $queryBuilder The doctrine query builder.
	 * @param string[]     $sort The sort array.
	 * @param string       $alias The doctrine alias of the entity.
	 *
	 * @return void
	 */
	public function addSort(QueryBuilder $queryBuilder, array $sort, string $alias): void
	{
		if (count($sort) > 0) {
			foreach ($sort as $s) {
				preg_match('/(?<direction>[+-]?)(?<field>\w.*)/', $s, $matches);

				if (empty($matches['field'])) {
					continue;
				}

				$normalizer = new CamelCaseToSnakeCaseNameConverter();
				$field = $normalizer->denormalize($matches['field']);

				$field = $this->getFieldFromMap($field);

				$direction = 'ASC';
				if ($matches['direction'] === '-') {
					$direction = 'DESC';
				}

				if ($field) {
					$queryBuilder->addOrderBy($alias.'.'.$field, $direction);
				}
			}
		}
	}

	/**
	 * Create the paginator.
	 *
	 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.10/tutorials/pagination.html
	 *
	 * @param QueryBuilder $queryBuilder The doctrine query builder.
	 * @param integer      $page The page number.
	 * @param integer      $perPage The number of items per page.
	 *
	 * @return Paginator The doctrine paginator.
	 */
	public function createPaginator(QueryBuilder $queryBuilder, int $page, int $perPage): Paginator
	{
		$queryBuilder->setFirstResult($perPage * ($page - 1));
		$queryBuilder->setMaxResults($perPage);

		return new Paginator($queryBuilder);
	}

	/**
	 * Check if a field exists in the map.
	 *
	 * @param string $field The field name.
	 * @return string|null The field name or null.
	 */
	public function getFieldFromMap(string $field): ?string
	{
		// Add the raw field names.
		$map = $this->getClassMetadata()->getFieldNames();

		if (in_array($field, $map)) {
			return $field;
		}

		return null;
	}

	/**
	 * Check if an association exists in the map.
	 *
	 * @param string $field
	 * @return string|null
	 */
	public function getAssociationFromMap(string $field): ?string
	{
		// Add the raw field names.
		$map = $this->getClassMetadata()->getAssociationNames();

		if (in_array($field, $map)) {
			return $field;
		}

		return null;
	}

	/**
	 * Add the desired filter to the query builder.
	 *
	 * @param QueryBuilder $queryBuilder The query builder.
	 * @param string       $name         The name of the filter.
	 * @param mixed        $value        The value of the filter.
	 * @param string       $alias        The alias of the entity.
	 * @param string       $parameter    The optional parameter name.
	 *
	 * @return void
	 */
	public function addFilter(QueryBuilder $queryBuilder, string $name, $value, string $alias, $parameter = null): void
	{
		$field = $this->getFieldFromMap($name);
		$association = $this->getAssociationFromMap($name);

		if (empty($field) && empty($association)) {
			return;
		}

		if (empty($value)) {
			return;
		}

		if ($parameter === null) {
			$parameter = $field ?? $association;
		}


		if (!empty($field)) {
			$where = sprintf('%s.%s LIKE :%s', $alias, $field, $parameter);
		} elseif (!empty($association)) {
			$where = sprintf('%1$s.%2$s IN(:%3$s)', $alias, $association, $parameter);
		}

		if (!empty($where)) {
			$queryBuilder->andWhere($where);
			$queryBuilder->setParameter($parameter, $value);
		}
	}
}
