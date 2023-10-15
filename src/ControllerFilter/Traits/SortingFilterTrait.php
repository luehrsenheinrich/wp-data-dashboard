<?php

declare(strict_types=1);

namespace App\ControllerFilter\Traits;

use JMS\Serializer\Annotation as Serializer;

trait SortingFilterTrait
{
	/**
	 * The fields to sort by.
	 *
	 * Array of strings that indicate field and direction. e.g. ['name', '-id']
	 * @var array<string Sort>
	 */
	protected $sort = [];

	public function getSort()
	{
		return $this->sort;
	}

	public function setSort(array $sort)
	{
		$this->sort = $sort;

		return $this;
	}
}
