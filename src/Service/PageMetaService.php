<?php
/**
 * A class that handles page metadata.
 */

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Validator\Constraints as Assert;
use Twig\Environment;

class PageMetaService
{
	/**
	 * The page title parts.
	 *
	 * @var string[]
	 */
	#[Assert\All([
		new Assert\Length(min: 1, max: 128),
	])]
	private array $titleParts = [];

	/**
	 * The site name.
	 *
	 * @var string
	 */
	private string $siteName = 'WP Data Dashboard';

	/**
	 * The title separator.
	 *
	 * @var string
	 */
	private string $titleSeparator = ' - ';

	/**
	 * The constructor.
	 */
	public function __construct(
		private Environment $twig,
	) {
	}

	/**
	 * Add a title part.
	 *
	 * @return self
	 */
	public function addTitlePart(string $titlePart): self
	{
		$this->titleParts[] = $titlePart;

		return $this;
	}

	/**
	 * Get the page title.
	 *
	 * @return string
	 */
	public function getTitle(): string
	{
		$titleParts = array_merge($this->titleParts, [$this->siteName]);

		return implode($this->titleSeparator, $titleParts);
	}

	/**
	 * Get the page meta tags.
	 *
	 * @return string The html meta tags for the page header.
	 */
	public function render(): string
	{
		return $this->twig->render(
			'pageMeta.html.twig',
			[
				'title' => $this->getTitle(),
			]
		);
	}
}
