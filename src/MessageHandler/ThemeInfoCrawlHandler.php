<?php
/**
 * The handler that handles the theme info crawl.
 */

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ThemeInfoCrawl;
use App\Service\OptionsService;
use App\Service\WpOrgApiCrawlService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class ThemeInfoCrawlHandler
{
	public function __construct(
		private WpOrgApiCrawlService $crawlService,
		private MessageBusInterface $bus,
		private LoggerInterface $logger,
		private OptionsService $optionsService,
	) {
	}

	/**
	 * Handle the message.
	 *
	 * @param ThemeInfoCrawl $message The message.
	 *
	 * @return void
	 */
	public function __invoke(ThemeInfoCrawl $message): void
	{
		$currentPage = $message->getPage();

		$this->logger->info('Crawling theme info page {page}.', [
			'page' => $currentPage,
		]);

		/**
		 * Request the theme infos from the API.
		 */
		$apiThemes = $this->crawlService->requestThemeInfos($currentPage);

		/**
		 * Ingest the theme infos.
		 */
		$this->crawlService->ingestThemes($apiThemes);

		/**
		 * Check if we have reached the end of the pages.
		 * If so, set the crawl state to finished.
		 */
		if ($apiThemes['info']['page'] >= $apiThemes['info']['pages']) {
			/**
			 * @var ThemeCrawlerStateOption $crawlState
			 */
			$crawlState = $this->optionsService->get('theme_info_crawler_state');
			$crawlState->setStatus('finished');
			$this->optionsService->set($crawlState);

			$this->logger->info('Finished the theme info crawl.');

			return;
		}

		/**
		 * Dispatch the crawl for the next page.
		 */
		$this->bus->dispatch(new ThemeInfoCrawl($currentPage + 1));
	}
}
