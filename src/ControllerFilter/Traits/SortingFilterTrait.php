<?php

declare(strict_types=1);

namespace App\ControllerFilter\Traits;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Attributes as OA;

trait SortingFilterTrait
{
	/**
	 * The fields to sort by.
	 *
	 * Array of strings that indicate field and direction. e.g. ['name', '-id']
	 * @var string[]
	 */
	#[Serializer\Type("array<string>")]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The fields to sort by.', example: ['name', '-id'])]
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
