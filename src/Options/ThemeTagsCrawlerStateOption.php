<?php
/**
 * An option class that holds the theme crawler state for the tags crawl.
 */

declare(strict_types=1);

namespace App\Options;

class ThemeTagsCrawlerStateOption extends AbstractOptions
{
	/**
	 * The start date and time of the crawl.
	 *
	 * @var \DateTimeInterface|null|string
	 */
	private $startDateTime;

	/**
	 * The status of the crawl.
	 * Can be 'running', 'finished'.
	 *
	 * @var string
	 */
	private ?string $status;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$now = new \DateTime("-10 years");

		$this->startDateTime = $now;
		$this->status = 'running';
	}

	/**
	 * Set the start date and time of the crawl.
	 *
	 * @param \DateTimeInterface $startDateTime The start date and time of the crawl.
	 *
	 * @return self
	 */
	public function setStartDateTime($startDateTime): self
	{
		if (is_string($startDateTime)) {
			$startDateTime = new \DateTimeImmutable($startDateTime);
		}

		$this->startDateTime = $startDateTime;

		return $this;
	}

	/**
	 * Get the start date and time of the crawl.
	 *
	 * @return \DateTimeInterface|null
	 */
	public function getStartDateTime(): ?\DateTimeInterface
	{
		if (is_string($this->startDateTime)) {
			$this->startDateTime = new \DateTimeImmutable($this->startDateTime);
		}

		return $this->startDateTime;
	}

	/**
	 * Set the status of the crawl.
	 * Can be 'running', 'finished'.
	 *
	 * @param string $status The status of the crawl.
	 *
	 * @return self
	 */
	public function setStatus(string $status): self
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * Get the status of the crawl.
	 * Can be 'running', 'finished'.
	 *
	 * @return string
	 */
	public function getStatus(): string
	{
		if (!$this->status) {
			$this->status = 'running';
		}

		return $this->status;
	}
}
