<?php

namespace App\Entity\EntityTraits;

use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column()]
	private ?int $id = null;

	/**
	 * Get the value of id
	 *
	 * @return integer|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * Set the value of id
	 *
	 * @param integer|null $id
	 *
	 * @return self
	 */
	public function setId(?int $id): self
	{
		$this->id = $id;

		return $this;
	}
}
