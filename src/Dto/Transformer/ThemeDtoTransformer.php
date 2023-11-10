<?php

declare(strict_types=1);

namespace App\Dto\Transformer;

use App\Dto\ThemeDto;
use App\Entity\Theme;

class ThemeDtoTransformer extends AbstractDtoTransformer
{
	/**
	 * {@inheritdoc}
	 */
	protected string $dtoObject = ThemeDto::class;

	/**
	 * {@inheritdoc}
	 */
	protected string $entityObject = Theme::class;

	/**
	 * {@inheritdoc}
	 *
	 * @param Theme $entity
	 *
	 * @return ThemeDto
	 */
	public function transformFromObject($entity): ThemeDto
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

		return $this->getDto();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param ThemeDto $dto
	 * @param Theme $entity|null
	 *
	 * @return Theme
	 */
	public function transformToObject($dto, $entity = null): Theme
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
			'name',
			'slug',
		]);

		return $this->getEntity();
	}
}
