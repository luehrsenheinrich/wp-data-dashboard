<?php

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Entity\EntityTraits\SetFromArrayTrait;
use App\Repository\ThemeStatSnapshotRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

#[ORM\Entity(repositoryClass: ThemeStatSnapshotRepository::class)]
class ThemeStatSnapshot implements TimestampableInterface
{
	use IdTrait, SetFromArrayTrait, TimestampableTrait;

	#[ORM\Column]
	private ?float $rating = null;

	#[ORM\Column]
	private ?int $numRatings = null;

	#[ORM\Column]
	private ?int $activeInstalls = null;

	#[ORM\Column]
	private ?int $downloaded = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: false)]
	private ?Theme $theme = null;

	#[ORM\Column]
	private ?float $usageScore = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): static
	{
		$this->id = $id;

		return $this;
	}

	public function getRating(): ?float
	{
		return $this->rating;
	}

	public function setRating(float $rating): static
	{
		$this->rating = $rating;

		return $this;
	}

	public function getNumRatings(): ?int
	{
		return $this->numRatings;
	}

	public function setNumRatings(int $numRatings): static
	{
		$this->numRatings = $numRatings;

		return $this;
	}

	public function getActiveInstalls(): ?int
	{
		return $this->activeInstalls;
	}

	public function setActiveInstalls(int $activeInstalls): static
	{
		$this->activeInstalls = $activeInstalls;

		return $this;
	}

	public function getDownloaded(): ?int
	{
		return $this->downloaded;
	}

	public function setDownloaded(int $downloaded): static
	{
		$this->downloaded = $downloaded;

		return $this;
	}

	public function getTheme(): ?Theme
	{
		return $this->theme;
	}

	public function setTheme(?Theme $theme): static
	{
		$this->theme = $theme;

		return $this;
	}

	public function getUsageScore(): ?float
	{
		return $this->usageScore;
	}

	public function setUsageScore(float $usageScore): static
	{
		$this->usageScore = $usageScore;

		return $this;
	}
}
