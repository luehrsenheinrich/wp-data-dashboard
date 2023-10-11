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

		if (!empty($apiThemes['themes'])) {
			/**
			 * Get a list of all the theme slugs.
			 */
			$themeSlugs = array_map(
				static fn (array $theme): string => $theme['slug'],
				$apiThemes['themes']
			);

			/**
			 * @var ThemeRepository $themeRepository
			 */
			$themeRepository = $this->doctrine->getRepository(Theme::class);
			$themeEntities = $themeRepository->findThemesBySlugs($themeSlugs);

			$entityManager = $this->doctrine->getManager();

			foreach ($apiThemes['themes'] as $apiTheme) {
				if (isset($themeEntities[$apiTheme['slug']])) {
					$themeEntity = $themeEntities[$apiTheme['slug']];
				} else {
					$themeEntity = null;
				}

				$this->crawlService->ingestTheme($apiTheme, $entityManager, $themeEntity);
			}

			$entityManager->flush();
			$entityManager->clear();
		}

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
