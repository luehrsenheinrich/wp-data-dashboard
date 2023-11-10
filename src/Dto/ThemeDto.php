<?php
/**
 * A data transfer object (DTO) to represent the theme entity.
 */

declare(strict_types=1);

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class ThemeDto implements DtoInterface
{
	/**
	 * The theme id.
	 *
	 * @var int
	 */
	#[Serializer\Type('int')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The theme id.', example: 1)]
	public int $id;

	/**
	 * The theme name.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read"])]
	#[Assert\NotBlank]
	#[OA\Property(description: 'The theme name.', example: 'Twenty Twenty-Two')]
	public string $name;

	/**
	 * The theme slug.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read"])]
	#[Assert\NotBlank]
	#[OA\Property(description: 'The theme slug.', example: 'twenty-twenty-two')]
	public string $slug;
}
