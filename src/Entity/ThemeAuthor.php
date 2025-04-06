<?php

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Repository\ThemeAuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\EntityTraits\SetFromArrayTrait;

/**
 * The author of a WordPress theme.
 */
#[ORM\Entity(repositoryClass: ThemeAuthorRepository::class)]
class ThemeAuthor
{
	use IdTrait, SetFromArrayTrait;

	#[ORM\Column(length: 255, unique: true)]
	private ?string $userNicename = null;

	#[ORM\Column(length: 255)]
	private ?string $profile = null;

	#[ORM\Column(length: 255)]
	private ?string $avatar = null;

	#[ORM\Column(length: 255)]
	private ?string $displayName = null;

	#[ORM\OneToMany(mappedBy: 'author', targetEntity: Theme::class, orphanRemoval: true)]
	private Collection $theme;

	public function __construct()
	{
		$this->theme = new ArrayCollection();
	}

	public function getUserNicename(): ?string
	{
		return $this->userNicename;
	}

	public function setUserNicename(string $userNicename): static
	{
		$this->userNicename = $userNicename;

		return $this;
	}

	public function getProfile(): ?string
	{
		return $this->profile;
	}

	public function setProfile(string $profile): static
	{
		$this->profile = $profile;

		return $this;
	}

	public function getAvatar(): ?string
	{
		return $this->avatar;
	}

	public function setAvatar(string $avatar): static
	{
		$this->avatar = $avatar;

		return $this;
	}

	public function getDisplayName(): ?string
	{
		return $this->displayName;
	}

	public function setDisplayName(string $displayName): static
	{
		$this->displayName = $displayName;

		return $this;
	}

	/**
	 * @return Collection<int, Theme>
	 */
	public function getTheme(): Collection
	{
		return $this->theme;
	}

	public function addTheme(Theme $theme): static
	{
		if (!$this->theme->contains($theme)) {
			$this->theme->add($theme);
			$theme->setAuthor($this);
		}

		return $this;
	}

	public function removeTheme(Theme $theme): static
	{
		if ($this->theme->removeElement($theme)) {
			// set the owning side to null (unless already changed)
			if ($theme->getAuthor() === $this) {
				$theme->setAuthor(null);
			}
		}

		return $this;
	}
}
