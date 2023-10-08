<?php

declare(strict_types=1);

namespace App\Entity\EntityTraits;

use function Symfony\Component\String\u;

/**
 * The trait that provides the ability to set properties from an array.
 */
trait SetFromArrayTrait
{
	/**
	 * Set properties from an array.
	 *
	 * @param array $items An array of property items.
	 *
	 * return self
	 */
	public function setFromArray(array $items): self
	{
		foreach ($items as $item => $value) {

			/**
			 * The setter method. It has to be valid CamelCase.
			 *
			 * @var string
			 */
			$setter = u('set'.ucfirst($item))->camel()->toString();

			if (is_callable([$this, $setter])) {
				$this->{$setter}($value);
			}
		}

		return $this;
	}
}
