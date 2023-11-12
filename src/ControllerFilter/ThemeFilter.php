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
	 * @var ?string
	 */
	#[Assert\Length(min:3, max:128)]
	#[OA\Property(description: 'The theme name.', example: 'Twenty Twenty-Two')]
	#[Serializer\Groups(["read"])]
	#[Serializer\Type('string')]
	private ?string $name = null;

	/**
	 * Get the value of name
	 *
	 * @return  ?string
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
