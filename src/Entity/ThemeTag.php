<?php

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Repository\ThemeTagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThemeTagRepository::class)]
class ThemeTag
{

	use IdTrait;

	#[ORM\Column(length: 255, unique: true)]
	private ?string $slug = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

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
}
