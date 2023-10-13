<?php

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Entity\EntityTraits\SetFromArrayTrait;
use App\Repository\ThemeStatSnapshotRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeStatSnapshotRepository::class)]
class ThemeStatSnapshot
{
	use IdTrait, SetFromArrayTrait;

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
	private ?float $freshness = null;

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

	public function getFreshness(): ?float
	{
		return $this->freshness;
	}

	public function setFreshness(float $freshness): static
	{
		$this->freshness = $freshness;

		return $this;
	}
}
