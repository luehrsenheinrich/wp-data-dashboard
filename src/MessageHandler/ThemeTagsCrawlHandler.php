<?php
/**
 * The handler that handles the theme tags crawl.
 */

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ThemeTagsCrawl;
use App\Service\WpOrgApiCrawlService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ThemeTagsCrawlHandler
{
	public function __construct(
		private LoggerInterface $logger,
		private WpOrgApiCrawlService $crawlService,
	) {
	}

	/**
	 * Handle the message.
	 *
	 * @param ThemeTagsCrawl $message The message.
	 *
	 * @return void
	 */
	public function __invoke(ThemeTagsCrawl $message): void
	{
		$this->logger->info('Crawling theme tags.');

		/**
		 * Request the theme tags from the API.
		 */
		$apiTags = $this->crawlService->requestThemeTags();

		$this->logger->info('Crawled {count} theme tags.', [
			'count' => count($apiTags),
		]);

		/**
		 * Ingest the theme tags.
		 */
		$this->crawlService->ingestTags($apiTags);
	}
}
