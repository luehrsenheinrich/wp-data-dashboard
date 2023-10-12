<?php
/**
 * The handler that handles the theme info crawl.
 */

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Theme;
use App\Message\ThemeInfoCrawl;
use App\Repository\ThemeRepository;
use App\Service\OptionsService;
use App\Service\WpOrgApiCrawlService;
use Doctrine\Persistence\ManagerRegistry;
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
		private ManagerRegistry $doctrine,
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

		$apiThemes = $this->crawlService->requestThemeInfos($currentPage);
		$this->crawlService->ingestThemes($apiThemes);

		/**
		 * Check if we have reached the end of the pages.
		 */
		if ($apiThemes['info']['page'] >= $apiThemes['info']['pages']) {

			/**
			 * @var ThemeCrawlerStateOption $crawlState
			 */
			$crawlState = $this->optionsService->get('theme_info_crawler_state');
			$this->logger->info('Finished the theme info crawl.');
			$crawlState->setStatus('finished');
			$this->optionsService->set($crawlState);

			return;
		}

		/**
		 * Dispatch the crawl for the next page.
		 */
		$this->bus->dispatch(new ThemeInfoCrawl($currentPage + 1));
	}
}
