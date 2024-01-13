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
	#[OA\Property(description: 'The theme slug.', example: 'twentytwentytwo')]
	public string $slug;

	/**
	 * The theme version.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read:theme"])]
	#[OA\Property(description: 'The theme version.', example: '1.0.0')]
	public string $version;

	/**
	 * The theme preview url.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read:theme"])]
	#[OA\Property(description: 'The theme preview url.', example: 'https://wp-themes.com/quickpress/')]
	public string $previewUrl;

	/**
	 * The theme screenshot url.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read:theme"])]
	#[OA\Property(description: 'The theme screenshot url.', example: '//ts.w.org/wp-content/themes/quickpress/screenshot.png?ver=1.9.2')]
	public string $screenshotUrl;

	/**
	 * The theme homepage.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read:theme"])]
	#[OA\Property(description: 'The theme homepage.', example: 'https://wordpress.org/themes/quickpress/')]
	public string $homepage;

	/**
	 * The theme description.
	 *
	 * @var string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read:theme"])]
	#[OA\Property(description: 'The theme description.', example: 'QuickPress is a minimalistic blogging ...')]
	public string $description;

	/**
	 * The theme template.
	 *
	 * @var string|null
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The theme template.', example: 'quickpress')]
	public ?string $template;

	/**
	 * The theme url.
	 *
	 * @var ?string
	 */
	#[Serializer\Type('string')]
	#[Serializer\Groups(["read:theme"])]
	#[OA\Property(description: 'The theme url.', example: 'https://www.quickonlinetips.com/archives/quickpress-wordpress-theme/')]
	public ?string $themeUrl;

	/**
	 * The theme last updated date.
	 *
	 * @var \DateTimeImmutable
	 */
	#[Serializer\Type('DateTimeInterface')]
	#[Serializer\Groups(["read:theme"])]
	#[OA\Property(description: 'The theme last updated date.', example: '2021-09-28T00:00:00+00:00')]
	public \DateTimeImmutable $lastUpdated;

	/**
	 * The theme rating.
	 *
	 * @var float
	 */
	#[Serializer\Type('float')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The theme rating.', example: 100)]
	public float $rating;

	/**
	 * The total theme ratings.
	 *
	 * @var int
	 */
	#[Serializer\Type('int')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The total theme ratings.', example: 100)]
	public int $numRatings;

	/**
	 * The total theme active installs.
	 *
	 * @var int
	 */
	#[Serializer\Type('int')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The total theme active installs.', example: 100)]
	public int $activeInstalls;

	/**
	 * The theme usage score.
	 *
	 * @var float
	 */
	#[Serializer\Type('float')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The theme usage score.', example: 100)]
	public float $usageScore;

	/**
	 * The total theme downloads.
	 *
	 * @var int
	 */
	#[Serializer\Type('int')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The total theme downloads.', example: 100)]
	public int $downloaded;

	/**
	 * The theme tags.
	 *
	 * @var ThemeTagDto[]
	 */
	#[Serializer\Type('array<App\Dto\ThemeTagDto>')]
	#[Serializer\Groups(["read"])]
	#[OA\Property(description: 'The theme tags.', example: [['id' => 1, 'name' => 'Accessibility Ready', 'slug' => 'accessibility-ready']])]
	public array $tags;
}
