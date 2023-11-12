<?php

declare(strict_types=1);

namespace App\ControllerFilter\Traits;

use JMS\Serializer\Annotation as Serializer;
use OpenApi\Attributes as OA;

trait OrderFilterTrait
{

	/**
	 * The field to order by.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[OA\Property(type: 'string', description: 'The field to order by.', example: 'id')]
	private string $orderBy = 'id';

	/**
	 * The direction to order by.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[OA\Property(type: 'string', description: 'The direction to order by.', example: 'ASC')]
	private string $order = 'ASC';

	/**
	 * Get the field to order by.
	 *
	 * @return string
	 */
	public function getOrderBy(): string
	{
		return $this->orderBy;
	}

	/**
	 * Set the field to order by.
	 *
	 * @param string $orderBy
	 * @return self
	 */
	public function setOrderBy(string $orderBy): self
	{
		$this->orderBy = $orderBy;

		return $this;
	}

	/**
	 * Get the direction to order by.
	 *
	 * @return string
	 */
	public function getOrder(): string
	{
		return $this->order;
	}

	/**
	 * Set the direction to order by.
	 *
	 * @param string $order
	 * @return self
	 */
	public function setOrder(string $order): self
	{
		$this->order = $order;

		return $this;
	}
}
