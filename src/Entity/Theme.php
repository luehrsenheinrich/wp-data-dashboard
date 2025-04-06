<?php

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Entity\EntityTraits\SetFromArrayTrait;
use App\Repository\ThemeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a WordPress theme.
 *
 * @see https://codex.wordpress.org/WordPress.org_API#Themes
 */
#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme implements TimestampableInterface
{
	use IdTrait, TimestampableTrait, SetFromArrayTrait;

	/**
	 * The theme's slug.
	 *
	 * @var string|null
	 */
	#[ORM\Column(nullable: false, unique: true)]
	#[Assert\NotBlank]
	private ?string $slug = null;

	/**
	 * The theme's name.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255)]
	private ?string $name = null;

	/**
	 * The theme's version.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255)]
	private ?string $version = null;

	/**
	 * The URL to the theme's preview.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255, nullable: true)]
	private ?string $previewUrl = null;

	/**
	 * The URL to the theme's screenshot.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255)]
	private ?string $screenshotUrl = null;

	/**
	 * The URL to the theme's homepage.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255)]
	private ?string $homepage = null;

	/**
	 * The theme's description.
	 *
	 * @var string|null
	 */
	#[ORM\Column(type: Types::TEXT)]
	private ?string $description = null;

	/**
	 * The theme's parent theme (or 'template').
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255, nullable: true)]
	private ?string $template = null;

	/**
	 * The URL to the theme's marketing page.
	 *
	 * @var string|null
	 */
	#[ORM\Column(length: 255, nullable: true)]
	private ?string $themeUrl = null;

	/**
	 * The date and time the theme was last updated.
	 *
	 * @var \DateTimeImmutable|null
	 */
	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	private ?\DateTimeImmutable $lastUpdated = null;

	/**
	 * The theme's tags.
	 *
	 * @var Collection<int, ThemeTag>
	 */
	#[ORM\ManyToMany(targetEntity: ThemeTag::class)]
	private Collection $tags;

	/**
	 * The theme's author.
	 *
	 * @var ThemeAuthor|null
	 */
	#[ORM\ManyToOne(inversedBy: 'theme', cascade: ['persist'], fetch: 'EAGER')]
	#[ORM\JoinColumn(nullable: false)]
	private ?ThemeAuthor $author = null;

	/**
	 * The stats snapshots for this theme.
	 *
	 * @var Collection<ThemeStatSnapshot>
	 */
	#[ORM\OneToMany(mappedBy: 'theme', targetEntity: ThemeStatSnapshot::class)]
	private Collection $themeStatSnapthots;

	/**
	 * The theme's rating.
	 *
	 * @var integer|null
	 */
	#[ORM\Column]
	private ?int $rating = null;

	/**
	 * The number of ratings the theme has received.
	 *
	 * @var integer|null
	 */
	#[ORM\Column]
	private ?int $numRatings = null;

	/**
	 * The number of active installs the theme has.
	 *
	 * @var integer|null
	 */
	#[ORM\Column]
	private ?int $activeInstalls = null;

	/**
	 * The number of times the theme has been downloaded.
	 *
	 * @var integer|null
	 */
	#[ORM\Column]
	private ?int $downloaded = null;

	/**
	 * The theme's usage score.
	 *
	 * @var float|null
	 */
	#[ORM\Column]
	private ?float $usageScore = null;

	/**
	 * The theme's parent theme.
	 *
	 * @var Theme|null
	 */
	#[ORM\ManyToOne(targetEntity: self::class, fetch: 'EAGER')]
	private ?self $parent = null;

	// ==========
	// Downloads per day
	// ==========

	/**
	 * The average number of downloads per day for the last 28 days.
	 *
	 * @var int|null
	 */
	#[ORM\Column]
	private ?int $dpdAvg28 = null; // phpcs:ignore Zend.NamingConventions.ValidVariableName.MemberVarContainsNumbers

	/**
	 * The average number of downloads per day for the last 7 days.
	 *
	 * @var int|null
	 */
	#[ORM\Column]
	private ?int $dpdAvg7 = null; // phpcs:ignore Zend.NamingConventions.ValidVariableName.MemberVarContainsNumbers

	/**
	 * The datetime of the last download count update.
	 *
	 * @var \DateTimeImmutable|null
	 */
	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	private ?\DateTimeImmutable $dpdLastUpdated = null;

	public function __construct()
	{
		$this->tags = new ArrayCollection();
		$this->themeStatSnapthots = new ArrayCollection();
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

	public function getScreenshotUrl(): ?string
	{
		return $this->screenshotUrl;
	}

	public function setScreenshotUrl(string $screenshotUrl): static
	{
		$this->screenshotUrl = $screenshotUrl;

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

	public function removeTags(): static
	{
		$this->tags->clear();

		return $this;
	}

	public function getAuthor(): ?ThemeAuthor
	{
		if ($this->author === null) {
			$this->author = new ThemeAuthor();
		}

		return $this->author;
	}

	public function setAuthor(?ThemeAuthor $author): static
	{
		$this->author = $author;

		return $this;
	}

	public function getThemeUrl(): ?string
	{
		return $this->themeUrl;
	}

	public function setThemeUrl(?string $themeUrl): static
	{
		$this->themeUrl = $themeUrl;

		return $this;
	}

	public function getLastUpdated(): ?\DateTimeImmutable
	{
		return $this->lastUpdated;
	}

	public function setLastUpdated(\DateTimeImmutable $lastUpdated): static
	{
		$this->lastUpdated = $lastUpdated;

		return $this;
	}

	/**
	 * @return Collection<int, ThemeStatSnapshot>
	 */
	public function getThemeStatSnapthots(): Collection
	{
		return $this->themeStatSnapthots;
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

	public function getDownloaded(): ?int
	{
		return $this->downloaded;
	}

	public function setDownloaded(int $downloaded): static
	{
		$this->downloaded = $downloaded;

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

	public function getParent(): ?self
	{
		return $this->parent;
	}

	public function setParent(?self $parent): static
	{
		$this->parent = $parent;

		return $this;
	}
}
