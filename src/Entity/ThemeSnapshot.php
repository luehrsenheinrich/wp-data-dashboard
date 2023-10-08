<?php

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Entity\EntityTraits\SetFromArrayTrait;
use App\Entity\EntityTraits\SlugTrait;
use App\Repository\ThemeSnapshotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ThemeSnapshotRepository::class)]
class ThemeSnapshot implements TimestampableInterface
{
	use IdTrait, TimestampableTrait, SetFromArrayTrait;

	#[ORM\Column(nullable: false)]
	#[Assert\NotBlank]
	private ?string $slug = null;

	#[ORM\Column(length: 255)]
	private ?string $name = null;

	#[ORM\Column(length: 255)]
	private ?string $version = null;

	#[ORM\Column(length: 255, nullable: true)]
	private ?string $previewUrl = null;

	#[ORM\Column(length: 255)]
	private ?string $author = null;

	#[ORM\Column(length: 255)]
	private ?string $screenshotUrl = null;

	#[ORM\Column]
	private ?int $rating = null;

	#[ORM\Column]
	private ?int $numRatings = null;

	#[ORM\Column]
	private ?int $activeInstalls = null;

	#[ORM\Column(length: 255)]
	private ?string $homepage = null;

	#[ORM\Column(type: Types::TEXT)]
	private ?string $description = null;

	#[ORM\Column(length: 255, nullable: true)]
	private ?string $template = null;

	#[ORM\ManyToMany(targetEntity: ThemeTag::class)]
	private Collection $tags;

	public function __construct()
	{
		$this->tags = new ArrayCollection();
	}

	/**
	 * Get the value of slug
	 *
	 * @return string|null
	 */
	public function getSlug(): ?string
	{
		return $this->slug;
	}

	/**
	 * Set the value of slug
	 *
	 * @param string|null $slug
	 *
	 * @return self
	 */
	public function setSlug(?string $slug): self
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

	public function getVersion(): ?string
	{
		return $this->version;
	}

	public function setVersion(string $version): static
	{
		$this->version = $version;

		return $this;
	}

	public function getPreviewUrl(): ?string
	{
		return $this->previewUrl;
	}

	public function setPreviewUrl(string $previewUrl): static
	{
		$this->previewUrl = $previewUrl;

		return $this;
	}

	public function getAuthor(): ?string
	{
		return $this->author;
	}

	public function setAuthor(string $author): static
	{
		$this->author = $author;

		return $this;
	}

	public function getScreenshotUrl(): ?string
	{
		return $this->screenshotUrl;
	}

	public function setScreenshotUrl(string $screenshotUrl): static
	{
		$this->screenshotUrl = $screenshotUrl;

		return $this;
	}

	public function getRating(): ?int
	{
		return $this->rating;
	}

	public function setRating(int $rating): static
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

	public function getHomepage(): ?string
	{
		return $this->homepage;
	}

	public function setHomepage(string $homepage): static
	{
		$this->homepage = $homepage;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(string $description): static
	{
		$this->description = $description;

		return $this;
	}

	public function getTemplate(): ?string
	{
		return $this->template;
	}

	public function setTemplate(?string $template): static
	{
		$this->template = $template;

		return $this;
	}

	/**
	 * @return Collection<int, ThemeTag>
	 */
	public function getTags(): Collection
	{
		return $this->tags;
	}

	public function addTag(ThemeTag $tag): static
	{
		if (!$this->tags->contains($tag)) {
			$this->tags->add($tag);
		}

		return $this;
	}

	public function removeTag(ThemeTag $tag): static
	{
		$this->tags->removeElement($tag);

		return $this;
	}
}
