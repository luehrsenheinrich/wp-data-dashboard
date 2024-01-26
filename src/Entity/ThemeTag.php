<?php

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Entity\EntityTraits\SetFromArrayTrait;
use App\Repository\ThemeTagRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

#[ORM\Entity(repositoryClass: ThemeTagRepository::class)]
class ThemeTag implements TimestampableInterface
{
	use IdTrait, SetFromArrayTrait, TimestampableTrait;

	/**
	 * The slug of the theme tag.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255, unique: true)]
	private ?string $slug = null;

	/**
	 * The name of the theme tag.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255)]
	private ?string $name = null;

	/**
	 * The number of themes that are tagged with this theme tag.
	 * This property is not persisted.
	 *
	 * @var int|null
	 */
	private ?int $themeCount = null;

	public function getSlug(): ?string
	{
		return $this->slug;
	}

	public function setSlug(string $slug): static
	{
		$this->slug = $slug;

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): static
	{
		$this->name = $name;

		return $this;
	}

	public function getThemeCount(): ?int
	{
		return $this->themeCount;
	}

	public function setThemeCount(int $themeCount): static
	{
		$this->themeCount = $themeCount;

		return $this;
	}
}
