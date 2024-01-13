<?php
/**
 * A data transfer object (DTO) to represent the theme tag entity.
 */

declare(strict_types=1);

namespace App\Dto;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Attributes as OA;

class ThemeTagDto implements DtoInterface
{
	/**
	 * The theme tag id.
	 *
	 * @var int
	 */
	#[Serializer\Type('int')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The theme tag id.', example: 1)]
	public int $id;

	/**
	 * The theme tag name.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read"])]
	#[Assert\NotBlank]
	#[OA\Property(description: 'The theme tag name.', example: 'Accessibility Ready')]
	public string $name;

	/**
	 * The theme tag slug.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read"])]
	#[Assert\NotBlank]
	#[OA\Property(description: 'The theme tag slug.', example: 'accessibility-ready')]
	public string $slug;
}
