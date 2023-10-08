<?php
/**
 * The class that stores the options.
 */

declare(strict_types=1);

namespace App\Entity;

use App\Entity\EntityTraits\IdTrait;
use App\Repository\OptionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The options entity.
 * It serves as a key-value store for our options.
 */
#[ORM\Entity(repositoryClass: OptionsRepository::class)]
class Options
{
	use IdTrait;

	#[ORM\Column(type: Types::TEXT, unique: true)]
	private ?string $type = null;

	#[ORM\Column(type: Types::JSON)]
	private ?array $value = null;

	public function getType(): ?string
	{
		return $this->type;
	}

	public function setType(string $type): static
	{
		$this->type = $type;

		return $this;
	}

	public function getValue(): ?array
	{
		return $this->value;
	}

	public function setValue(array $value): static
	{
		$this->value = $value;

		return $this;
	}
}
