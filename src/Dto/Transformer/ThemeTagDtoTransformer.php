<?php

declare(strict_types=1);

namespace App\Dto\Transformer;

use App\Dto\ThemeTagDto;
use App\Entity\ThemeTag;

class ThemeTagDtoTransformer extends AbstractDtoTransformer
{
	/**
	 * {@inheritdoc}
	 */
	protected string $dtoObject = ThemeTagDto::class;

	/**
	 * {@inheritdoc}
	 */
	protected string $entityObject = ThemeTag::class;

	/**
	 * {@inheritdoc}
	 *
	 * @param ThemeTag $entity
	 *
	 * @return ThemeTagDto
	 */
	public function transformFromObject($entity): ThemeTagDto
	{
		/**
		 * Create a fresh DTO object.
		 */
		$this->createDto();

		/**
		 * Set the entity object.
		 */
		$this->setEntity($entity);

		/**
		 * Set some basic properties.
		 */
		$this->setDtoProperties([
			'id',
			'name',
			'slug',
		]);

		return $this->dto;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param ThemeTagDto $dto
	 * @param ThemeTag    $entity
	 *
	 * @return ThemeTag
	 */
	public function transformToObject($dto, $entity = null): ThemeTag
	{
		$this->maybeCreateEntity($entity);

		/**
		 * Set the DTO object.
		 */
		$this->setDto($dto);

		/**
		 * Set some basic properties.
		 */
		$this->setEntityProperties([
			'id',
			'name',
			'slug',
		]);

		return $this->entity;
	}
}
