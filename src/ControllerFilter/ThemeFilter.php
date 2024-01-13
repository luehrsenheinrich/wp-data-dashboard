<?php

declare(strict_types=1);

namespace App\ControllerFilter;

use App\ControllerFilter\Traits\OrderFilterTrait;
use App\ControllerFilter\Traits\PaginationFilterTrait;
use App\ControllerFilter\Traits\SortingFilterTrait;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;
use JMS\Serializer\Annotation as Serializer;

class ThemeFilter extends AbstractFilter
{
	use PaginationFilterTrait, OrderFilterTrait;

	/**
	 * Search for themes.
	 * This keyword will be used to search for themes in the name and description.
	 *
	 * @var ?string
	 */
	#[Assert\Length(min:3, max:128)]
	#[OA\Property(description: 'Search for themes.', example: 'Twenty Twenty-Two')]
	#[Serializer\Groups(["read"])]
	#[Serializer\Type('string')]
	private ?string $search = null;

	/**
	 * Filter by theme tag ids.
	 *
	 * @var ?int[]
	 */
	#[OA\Property(description: 'Filter by theme tag ids.', example: [1, 2])]
	#[Serializer\Groups(["read"])]
	#[Serializer\Type('array<int>')]
	private $themeTagIds = null;

	/**
	 * Get the search keyword.
	 *
	 * @return ?string
	 */
	public function getSearch(): ?string
	{
		return $this->search;
	}

	/**
	 * Set the search keyword.
	 *
	 * @param ?string $search
	 * @return self
	 */
	public function setSearch(?string $search): self
	{
		$this->search = $search;

		return $this;
	}

	/**
	 * Get the theme tag ids.
	 *
	 * @return ?int[]
	 */
	public function getThemeTagIds(): ?array
	{
		return $this->themeTagIds;
	}

	/**
	 * Set the theme tag ids.
	 *
	 * @param ?int[] $themeTagIds
	 * @return self
	 */
	public function setThemeTagIds(?array $themeTagIds): self
	{
		$this->themeTagIds = $themeTagIds;

		return $this;
	}
}
