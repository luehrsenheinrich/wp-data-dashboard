<?php

declare(strict_types=1);

namespace App\ControllerFilter;

use App\ControllerFilter\Traits\PaginationFilterTrait;
use App\ControllerFilter\Traits\SortingFilterTrait;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class ThemeFilter extends AbstractFilter
{
	use PaginationFilterTrait, SortingFilterTrait;

	/**
	 * @var string
	 */
	#[Assert\Length(min:3, max:128)]
	#[OA\Property(description: 'The theme name.', example: 'Twenty Twenty-Two')]
	private $name;

	/**
	 * Get the value of name
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the value of name
	 *
	 * @param  string  $name
	 *
	 * @return  self
	 */
	public function setName(string $name)
	{
		$this->name = $name;

		return $this;
	}
}
