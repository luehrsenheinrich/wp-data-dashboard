<?php
/**
 * A message that holds information about the theme tags crawl.
 */

declare(strict_types=1);

namespace App\Message;

class ThemeTagsCrawl
{
	public function __construct(
		private int $page,
	) {
	}

	/**
	 * Get the page.
	 *
	 * @return int
	 */
	public function getPage(): int
	{
		return $this->page;
	}

	/**
	 * Set the page.
	 *
	 * @param int $page The page.
	 *
	 * @return self
	 */
	public function setPage(int $page): self
	{
		$this->page = $page;

		return $this;
	}
}
