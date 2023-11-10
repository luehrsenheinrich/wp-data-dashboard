<?php
/**
 * This file handles the abstract DTO transformer.
 */

declare(strict_types=1);

namespace App\Dto\Transformer;

use App\Dto\DtoInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class provides a base for DTO transformers.
 * It provides the basic functionality for transforming DTOs to entity objects and vice versa.
 */
abstract class AbstractDtoTransformer implements DtoTransformerInterface
{
	/**
	 * The DTO object class.
	 * This is used to create a new instance of the DTO object.
	 *
	 * @var string
	 */
	protected string $dtoObject;

	/**
	 * The DTO object.
	 * The DTO object is saved here for transformation purposes.
	 *
	 * @var DtoInterface|null
	 */
	protected ?DtoInterface $dto = null;

	/**
	 * The Entity object class.
	 * This is used to create a new instance of the Entity object.
	 *
	 * @var string
	 */
	protected string $entityObject;

	/**
	 * The Entity object.
	 * The Entity object is saved here for transformation purposes.
	 *
	 * @var object|null
	 */
	protected ?object $entity = null;

	/**
	 * {@inheritdoc}
	 */
	public function transformFromObjects(iterable $objects): iterable
	{
		$dtos = [];
		foreach ($objects as $object) {
			$dtos[] = $this->transformFromObject($object);
		}

		return $dtos;
	}

	/**
	 * @inheritDoc
	 */
	public function transformToObjects(iterable $dtos, iterable $objects = null): iterable
	{
		if (null === $objects) {
			$objects = new ArrayCollection();
		}

		$dtos = [];
		foreach ($dtos as $key => $dto) {
			$object = $objects[$key] ?? null;
			$dtos[] = $this->transformToObject($dto, $object);
		}

		return new ArrayCollection($dtos);
	}

	/**
	 * Sets the DTO object.
	 *
	 * @param DtoInterface|null $dto
	 *
	 * @return void
	 */
	public function setDto(?DtoInterface $dto): void
	{
		if ($dto === null) {
			$this->createDto();
		} else {
			$this->dto = $dto;
		}
	}

	/**
	 * Gets the DTO object.
	 *
	 * @return DtoInterface|null
	 */
	public function getDto(): ?DtoInterface
	{
		if (null === $this->dto) {
			$this->createDto();
		}

		return $this->dto;
	}

	/**
	 * Creates a new DTO object.
	 *
	 * @return DtoInterface
	 */
	protected function createDto(): DtoInterface
	{
		$this->dto = new $this->dtoObject();

		return $this->dto;
	}

	/**
	 * Set a dto property.
	 *
	 * @param string $property
	 * @param callable|mixed|null $getterOrValue The getter method or the value.
	 */
	protected function setDtoProperty(string $property, $getterOrValue = null): void
	{
		$value = null;

		if (is_callable($getterOrValue)) {
			// If the getterOrValue is a callable, call it.
			$value = $getterOrValue();
		} elseif (null !== $getterOrValue) {
			// If the getterOrValue is not null, set it as the value.
			$value = $getterOrValue;
		}

		// If the value is not null, set it on the DTO.
		if ($value !== null) {
			$this->dto->$property = $value;
			return;
		}

		/**
		 * Try to guess the getter method on the entity object.
		 */
		$getter = 'get'.ucfirst($property);
		if (method_exists($this->getEntity(), $getter)) {
			$value = $this->getEntity()->$getter();
		}

		$this->dto->$property = $value;
	}

	/**
	 * Set dto properties.
	 *
	 * @param string[] $properties
	 */
	protected function setDtoProperties(array $properties): void
	{
		foreach ($properties as $property) {
			$this->setDtoProperty($property);
		}
	}

	/**
	 * Sets the Entity object.
	 *
	 * @param object $entity
	 *
	 * @return this
	 */
	public function setEntity(object $entity): self
	{
		$this->entity = $entity;

		return $this;
	}

	/**
	 * Gets the Entity object.
	 *
	 * @return object|null
	 */
	public function getEntity(): ?object
	{
		if (null === $this->entity) {
			$this->createEntity();
		}

		return $this->entity;
	}

	/**
	 * Creates a new Entity object.
	 *
	 * @return object
	 */
	protected function createEntity(): object
	{
		$this->entity = new $this->entityObject();

		return $this->entity;
	}

	/**
	 * Maybe create a new entity object.
	 * If the given entity is null, a new entity object will be created.
	 *
	 * @param object|null $entity The entity object.
	 *
	 * @return object
	 */
	protected function maybeCreateEntity(?object $entity): object
	{
		// Check if we have a user object
		if (is_a($entity, $this->entityObject)) {
			// We have a user object, so we can use it
			$this->setEntity($entity);
		} else {
			// We don't have a user object, so we create a new one
			$this->createEntity();
		}

		return $this->getEntity();
	}

	/**
	 * Set an entity property.
	 *
	 * @param string $propertyName The property name of the entity we want to set.
	 * @param mixed|null $value The value we want to set. Usually the value of the DTO property.
	 * @param callable|null $setter The setter method name on the entity for this property.
	 * @param callable|null $getter The getter method name on the entity for this property.
	 *
	 * @return this
	 */
	protected function setEntityProperty(string $propertyName, mixed $value = null, ?callable $setter = null, ?callable $getter = null): self
	{

		/**
		 * Guess the entity setter method name.
		 */
		$setterName = 'set'.ucfirst($propertyName);

		/**
		 * If the value is null, try to get the value from the dto.
		 * We ignore the value if it is an DtoInterface object.
		 */
		if (null === $value
			&& !is_a($this->dto->$propertyName, DtoInterface::class)
		) {
			$value = $this->dto->$propertyName;
		}

		/**
		 * Normalize the value.
		 */
		$newValue = $value ?? $this->getEntityPropertyValue($propertyName, $getter);

		/**
		 * If the setter method is set, use it.
		 */
		if (null !== $setter) {
			call_user_func($setter, $newValue);

			return $this;
		}

		/**
		 * If the setter method exists, use it.
		 */
		if (method_exists($this->entity, $setterName)) {
			call_user_func([$this->entity, $setterName], $newValue);
		}

		return $this;
	}

	/**
	 * Set entity properties.
	 *
	 * @param string[] $properties
	 */
	protected function setEntityProperties(array $properties): void
	{
		foreach ($properties as $property) {
			$this->setEntityProperty($property);
		}
	}

	/**
	 * Unset an entity property.
	 * In our case, we set the property to null.
	 *
	 * @param string $propertyName The property name of the entity we want to set.
	 * @param callable|null $setter The setter method name on the entity for this property.
	 *
	 * @return this
	 */
	protected function unsetEntityProperty(string $propertyName, ?callable $setter = null): self
	{
		/**
		 * Guess the entity setter method name.
		 */
		$setterName = 'set'.ucfirst($propertyName);

		/**
		 * If the setter method is set, use it.
		 */
		if (null !== $setter) {
			call_user_func($setter, null);

			return $this;
		}

		/**
		 * If the setter method exists, use it.
		 */
		if (method_exists($this->entity, $setterName)) {
			call_user_func([$this->entity, $setterName], null);
		}

		return $this;
	}


	/**
	 * Get the value of an entity property.
	 *
	 * @param string $propertyName The property name of the entity we want to get.
	 * @param callable|null $getter The getter method name on the entity for this property.
	 *
	 * @return mixed
	 */
	private function getEntityPropertyValue($propertyName, $getter = null)
	{
		/**
		 * Guess the entity getter method name.
		 */
		$getterName = 'get'.ucfirst($propertyName);

		if (null !== $getter && is_callable($getter)) {
			$entityValue = call_user_func($getter);
		} elseif (method_exists($this->entity, $getterName)) {
			$entityValue = call_user_func([$this->entity, $getterName]);
		}

		return $entityValue;
	}
}
