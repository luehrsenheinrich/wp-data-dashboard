<?php

namespace App\Controller\Api;

use App\Dto\DtoInterface;
use App\Dto\Transformer\DtoTransformerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractLHRestController extends AbstractFOSRestController
{

	/**
	 * @var ValidatorInterface
	 */
	protected $validator;

	/**
	 * @var ManagerRegistry
	 */
	protected $doctrine;

	/**
	 * Take a DTO, process it, validate it and persist it.
	 *
	 * @param DtoInterface $dto The dto to process.
	 * @param DtoTransformerInterface $transformer The transformer to use.
	 * @param object $object The entity to persist.
	 *
	 * @return object The persisted entity.
	 */
	protected function mapAndPersistDTO(DtoInterface $dto, DtoTransformerInterface $transformer, $object): Object
	{
		// Transform DTO to object.
		$newObject = $transformer->transformToObject($dto, $object);

		// Check if the object is valid.
		$this->validateEntity($newObject);

		// Persist the object in the database.
		$entityManager = $this->doctrine->getManager();
		$entityManager->persist($newObject);

		$entityManager->flush();

		return $object;
	}

	/**
	 * Check if the given enity is valid.
	 *
	 * @param Object $entity The entity to validate.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function validateEntity($entity): void
	{
		$errors = $this->validator->validate($entity);
		$this->handleValidationErrors($errors);
	}

	/**
	 * Handle the validation errors that might have occurred.
	 *
	 * @param ConstraintViolationListInterface $errors The validation errors.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function handleValidationErrors(ConstraintViolationListInterface $errors): void
	{
		if (count($errors) > 0) {
			$violations = [];

			foreach ($errors as $error) {
				$violations[] = $error->getPropertyPath().': '.$error->getMessage();
			}

			throw new \InvalidArgumentException(implode(', ', $violations));
		}
	}


	#[Required]
	public function injectDependencies(
		ManagerRegistry $doctrine,
		ValidatorInterface $validator
	) {
		$this->doctrine = $doctrine;
		$this->validator = $validator;
	}
}
