<?php

declare(strict_types=1);

namespace App\Dto\Transformer;

use App\Dto\DtoInterface;

interface DtoTransformerInterface
{
	/**
	 * Transform an entity object into a DTO.
	 */
	public function transformFromObject($object): DtoInterface;

	/**
	 * Transform a collection of entity objects into a collection of DTOs.
	 */
	public function transformFromObjects(iterable $objects): iterable;

	/**
	 * Transform a DTO to an entity object.
	 *
	 * @param DtoInterface $dto    The DTO that should be transformed to an object.
	 * @param Object       $object The existing or new object to map the DTO to.
	 *
	 * @return Object
	 */
	public function transformToObject(DtoInterface $dto, $object = null): Object;

	/**
	 * Transform a collection of DTOs to an entity object.
	 *
	 * @param iterable $dtos    The DTOs that should be transformed to objects.
	 * @param iterable $objects The existing or new objects to map the DTOs to.
	 *
	 * @return iterable
	 */
	public function transformToObjects(iterable $dto, iterable $objects): iterable;
}
