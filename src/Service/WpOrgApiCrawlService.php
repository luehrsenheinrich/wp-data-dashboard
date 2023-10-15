<?php
/**
 * The class that helps us crawl the WordPress.org API.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Theme;
use App\Entity\ThemeAuthor;
use App\Entity\ThemeStatSnapshot;
use App\Entity\ThemeTag;
use App\Message\ThemeInfoCrawl;
use App\Message\ThemeStatsCrawl;
use App\Message\ThemeTagsCrawl;
use App\Options\ThemeCrawlerStateOption;
use App\Options\ThemeStatsCrawlerStateOption;
use App\Repository\ThemeAuthorRepository;
use App\Repository\ThemeRepository;
use App\Repository\ThemeTagRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WpOrgApiCrawlService
{

	/**
	 * The base url of the WordPress.org API.
	 *
	 * @var string
	 */
	protected $host = 'https://api.wordpress.org';

	/**
	 * The theme endpoint of the WordPress.org API.
	 *
	 * @var string
	 */
	protected $themeEndpoint = '/themes/info/1.1/';

	/**
	 * The number of records to request per page.
	 *
	 * @var int
	 */
	protected $perPage = 1000;

	/**
	 * The number of records to request per page for a large crawl.
	 *
	 * @var int
	 */
	protected $perPageLarge = 10000;

	/**
	 * The datetime offset after which we need to crawl again.
	 */
	protected $dateTimeOffset = '-5 minutes';

	/**
	 * The datetime offset after which we need to crawl again for a fast crawl.
	 */
	protected $dateTimeOffsetFast = '-1 minute';

	/**
	 * An array of ThemeAuthor entities indexed by their user_nicename.
	 *
	 * @var ThemeAuthor[]
	 */
	protected $themeAuthors = [];

	/**
	 * An array of ThemeTag entities indexed by their slug.
	 *
	 * @var ThemeTag[]
	 */
	protected $themeTags = [];

	/**
	 * An array of Theme entities indexed by their slug.
	 *
	 * @var Theme[]
	 */
	protected $themes = [];

	/**
	 * The constructor.
	 */
	public function __construct(
		private HttpClientInterface $client,
		private ManagerRegistry $doctrine,
		private OptionsService $optionsService,
		private LoggerInterface $logger,
		private ContainerBagInterface $params,
		private ThemeTagRepository $themeTagRepository,
		private MessageBusInterface $bus,
	) {
		$this->dateTimeOffset = $this->params->get('app.wp_crawl_time_offset');
		$this->dateTimeOffsetFast = $this->params->get('app.wp_crawl_time_offset_fast');

		$this->perPage = intval($this->params->get('app.wp_crawl_per_page'));
		$this->perPageLarge = intval($this->params->get('app.wp_crawl_per_page_large'));
	}

	/**
	 * Perform the actual request to the WordPress.org API.
	 *
	 * @param string $endpoint The endpoint to request.
	 * @param array  $params The parameters to send with the request.
	 *
	 * @return array The response from the API.
	 */
	protected function request(string $endpoint, array $params = []): array
	{
		// Make an http request to the themes api
		$response = $this->client->request(
			'GET',
			$this->host.$endpoint,
			[
				'headers' => [
					'Accept' => 'application/json',
				],
				'query' => $params,
			]
		);

		return $response->toArray();
	}

	/**
	 * Request theme infos from the WordPress.org API.
	 * This method serves to fetch the more static theme data from the API.
	 *
	 * @param int $page The page to request.
	 *
	 * @return array The themes from the API.
	 */
	public function requestThemeInfos(int $page = 1): array
	{
		$params = [
			'action' => 'query_themes',
			'request[page]' => $page,
			'request[per_page]' => $this->perPage,
			'request[fields][active_installs]' => 0,
			'request[fields][num_ratings]' => 0,
			'request[fields][rating]' => 0,
			'request[fields][extended_author]' => 1,
			'request[fields][tags]' => 1,
			'request[fields][theme_url]' => 1,
			'request[fields][last_updated]' => 1,
		];

		$this->logger->info('Requesting themes from WordPress.org API.', [
			'params' => $params,
		]);

		return $this->request($this->themeEndpoint, $params);
	}

	/**
	 * Request theme stats from the WordPress.org API.
	 * This method serves to fetch the more dynamic theme data from the API.
	 *
	 * @param int $page The page to request.
	 *
	 * @return array The themes from the API.
	 */
	public function requestThemeStats(int $page = 1): array
	{
		$params = [
			'action' => 'query_themes',
			'request[page]' => $page,
			'request[per_page]' => $this->perPageLarge,
			'request[fields][active_installs]' => 1,
			'request[fields][num_ratings]' => 1,
			'request[fields][rating]' => 1,
			'request[fields][downloaded]' => 1,
			'request[fields][description]' => 0,
			'request[fields][homepage]' => 0,
			'request[fields][template]' => 0,
			'request[fields][screenshot_url]' => 0,
		];

		$this->logger->info('Requesting theme stats from WordPress.org API.', [
			'params' => $params,
		]);

		return $this->request($this->themeEndpoint, $params);
	}

	/**
	 * Request the theme tags from the WordPress.org API.
	 *
	 * @return array The theme tags from the API.
	 */
	public function requestThemeTags(): array
	{
		$params = [
			'action' => 'hot_tags',
		];

		$this->logger->info('Requesting theme tags from WordPress.org API.', [
			'params' => $params,
		]);

		return $this->request($this->themeEndpoint, $params);
	}

	/**
	 * Crawl and ingest the theme infos from the WordPress.org API.
	 * This method does not have properties, as it handles the crawl state
	 * with an option from the database.
	 *
	 * @return void
	 */
	public function maybeCrawlThemeInfos()
	{
		/**
		 * @var ThemeCrawlerStateOption $crawlState
		 */
		$crawlState = $this->optionsService->get('theme_info_crawler_state');

		/**
		 * If no crawl date time is set, set it to 0.
		 */
		if (!$crawlState->getStartDateTime()) {
			$crawlState->setStartDateTime(new \DateTimeImmutable("-10 years"));
		}

		/**
		 * A datetime in the past that we use to check if we need to crawl.
		 *
		 * @var \DateTimeInterface $crawlDateTimeOffset
		 */
		$crawlDateTimeOffset = new \DateTimeImmutable($this->dateTimeOffsetFast);

		/**
		 * Check if we actually need to crawl.
		 * If the state is finished, but the datetime offset has been reached,
		 * start a new crawl.
		 */
		if ($crawlState->getStartDateTime() < $crawlDateTimeOffset) {
			$this->logger->info('Starting a new theme crawl.');
			$crawlState->setStatus('running');
			$crawlState->setStartDateTime(new \DateTimeImmutable());
			$this->optionsService->set($crawlState);
		} else {
			$this->logger->info('Theme crawl already running.');
			return null;
		}

		/**
		 * Send the initial crawl of the first page to the messenger bus.
		 */
		$this->bus->dispatch(new ThemeInfoCrawl(1));
	}

	/**
	 * Crawl and ingest the theme tags from the WordPress.org API.
	 * This method does not have properties, as it handles the crawl state
	 * with an option from the database.
	 *
	 * @return void
	 */
	public function maybeCrawlThemeTags()
	{
		/**
		 * @var ThemeCrawlerStateOption $crawlState
		 */
		$crawlState = $this->optionsService->get('theme_tags_crawler_state');

		/**
		 * A datetime in the past that we use to check if we need to crawl.
		 *
		 * @var \DateTimeInterface $crawlDateTimeOffset
		 */
		$crawlDateTimeOffset = new \DateTimeImmutable($this->dateTimeOffsetFast);

		/**
		 * Check if we actually need to crawl.
		 * If the state is finished, but the datetime offset has been reached,
		 * start a new crawl.
		 */
		if ($crawlState->getStartDateTime() < $crawlDateTimeOffset) {
			$this->logger->info('Starting a new theme crawl.');
			$crawlState->setStatus('running');
			$crawlState->setStartDateTime(new \DateTimeImmutable());
			$this->optionsService->set($crawlState);
		} else {
			$this->logger->info('Theme crawl already running.');
			return null;
		}

		/**
		 * Send the initial crawl of the first page to the messenger bus.
		 */
		$this->bus->dispatch(new ThemeTagsCrawl());
	}

	/**
	 * Crawl and ingest the theme stats from the WordPress.org API.
	 * This method does not have properties, as it handles the crawl state
	 * with an option from the database.
	 *
	 * @return void
	 */
	public function maybeCrawlThemeStats()
	{
		/**
		 * @var ThemeStatsCrawlerStateOption $crawlState
		 */
		$crawlState = $this->optionsService->get('theme_stats_crawler_state');

		/**
		 * A datetime in the past that we use to check if we need to crawl.
		 *
		 * @var \DateTimeInterface $crawlDateTimeOffset
		 */
		$crawlDateTimeOffset = new \DateTimeImmutable($this->dateTimeOffset);

		/**
		 * Check if we actually need to crawl.
		 * If the state is finished, but the datetime offset has been reached,
		 * start a new crawl.
		 */
		if ($crawlState->getStartDateTime() < $crawlDateTimeOffset) {
			$this->logger->info('Starting a new theme stats crawl.');
			$crawlState->setStatus('running');
			$crawlState->setStartDateTime(new \DateTimeImmutable());
			$this->optionsService->set($crawlState);
		} else {
			$this->logger->info('Theme stats crawl already running.');
			return null;
		}

		/**
		 * Send the initial crawl of the first page to the messenger bus.
		 */
		$this->bus->dispatch(new ThemeStatsCrawl(1));
	}

	/**
	 * The method that ingests the themes from the WordPress.org API.
	 *
	 * @param array $apiThemes The themes from the API.
	 *
	 * @return void
	 */
	public function ingestThemes(array $apiThemes)
	{
		if (!empty($apiThemes['themes'])) {
			/**
			 * Get a list of all the theme slugs.
			 */
			$themeSlugs = array_map(
				static fn (array $theme): string => $theme['slug'],
				$apiThemes['themes']
			);

			/**
			 * Get a list of all the theme author nicenames.
			 */
			$nicenames = array_map(
				static fn (array $theme): string => $theme['author']['user_nicename'],
				$apiThemes['themes']
			);

			/**
			 * Get a list of all the theme tags.
			 * The slug of the tags is the key of the array.
			 */
			$tagSlugs = [];
			foreach ($apiThemes['themes'] as $theme) {
				if (!empty($theme['tags'])) {
					$tagSlugs = array_unique(array_merge($tagSlugs, array_keys($theme['tags'])));
				}
			}

			/**
			 * @var ThemeRepository $themeRepository
			 */
			$themeRepository = $this->doctrine->getRepository(Theme::class);
			$themeEntities = $themeRepository->findThemesBySlugs($themeSlugs);

			/**
			 * @var ThemeAuthorRepository $themeAuthorRepository
			 */
			$themeAuthorRepository = $this->doctrine->getRepository(ThemeAuthor::class);
			$this->themeAuthors = $themeAuthorRepository->findByUserNicenames($nicenames);

			/**
			 * @var ThemeTagRepository $themeTagRepository
			 */
			$themeTagRepository = $this->doctrine->getRepository(ThemeTag::class);
			$this->themeTags = $themeTagRepository->findBySlugs($tagSlugs);

			$entityManager = $this->doctrine->getManager();

			foreach ($apiThemes['themes'] as $apiTheme) {
				if (isset($themeEntities[$apiTheme['slug']])) {
					$themeEntity = $themeEntities[$apiTheme['slug']];
				} else {
					$themeEntity = null;
				}

				$this->ingestThemeInfos($apiTheme, $entityManager, $themeEntity);
			}

			$entityManager->flush();
			$entityManager->clear();
		}
	}

	/**
	 * Ingest a theme from the WordPress.org API into the database.
	 *
	 * @param array $theme The theme to ingest.
	 * @param EntityManagerInterface $entityManager The entity manager to use.
	 *
	 * @return void
	 */
	public function ingestThemeInfos(array $theme, ObjectManager $entityManager, ?Theme $themeEntity = null): void
	{
		/*
		 * Fix some type issues.
		 */
		if (!empty($theme['version'])) {
			$theme['version'] = (string) $theme['version'];
		}

		/**
		 * Fix the false state of the [author][author_url] property.
		 */
		if (isset($theme['author']['author_url']) && $theme['author']['author_url'] === false) {
			$theme['author']['author_url'] = null;
		}

		/**
		 * Fix the false state of the [author][author] property.
		 */
		if (isset($theme['author']['author']) && $theme['author']['author'] === false) {
			$theme['author']['author'] = null;
		}

		if ($themeEntity === null) {
			$themeEntity = new Theme();
		}

		/**
		 * Handle the theme author.
		 */
		if (isset($this->themeAuthors[$theme['author']['user_nicename']]) && $this->themeAuthors[$theme['author']['user_nicename']] instanceof ThemeAuthor) {
			$themeAuthor = $this->themeAuthors[$theme['author']['user_nicename']];
		} else {
			$themeAuthor = $themeEntity->getAuthor();
			$this->themeAuthors[$theme['author']['user_nicename']] = $themeAuthor;
		}

		$themeAuthor->setFromArray($theme['author']);
		unset($theme['author']);

		/**
		 * Handle the theme tags.
		 */
		if (!empty($theme['tags'])) {
			// Clear the existing tags.
			$themeEntity->removeTags();

			//Get the theme tag slugs from the keys of the array.
			$themeTagSlugs = array_keys($theme['tags']);

			foreach ($themeTagSlugs as $themeTagSlug) {
				if (isset($this->themeTags[$themeTagSlug]) && $this->themeTags[$themeTagSlug] instanceof ThemeTag) {
					$themeTag = $this->themeTags[$themeTagSlug];
					$themeEntity->addTag($themeTag);
				}
			}

			unset($theme['tags']);
		}

		/**
		 * Handle theme_url.
		 */
		if (isset($theme['theme_url']) && $theme['theme_url'] === false) {
			$theme['theme_url'] = null;
		}

		/**
		 * Handle last updated. (last_updated_time)
		 */
		if (isset($theme['last_updated_time'])) {
			$themeEntity->setLastUpdated(new \DateTimeImmutable($theme['last_updated_time']));
			unset($theme['last_updated_time']);
			unset($theme['last_updated']);
		}


		$themeEntity->setFromArray($theme);
		$themeEntity->setAuthor($themeAuthor);

		$entityManager->persist($themeEntity);
		$entityManager->flush();
	}

	/**
	 * The method that ingests the theme tags from the WordPress.org API.
	 *
	 * @param array $apiTags The theme tags from the API.
	 *
	 * @return void
	 */
	public function ingestTags(array $apiTags)
	{
		if (!empty($apiTags)) {
			$tagSlugs = array_map(
				static fn (array $tag): string => $tag['slug'],
				$apiTags
			);

			$this->logger->info('Ingesting {count} theme tags.', [
				'count' => count($apiTags),
			]);

			/**
			 * @var ThemeTagRepository $themeTagRepository
			 */
			$themeTagRepository = $this->doctrine->getRepository(ThemeTag::class);
			$themeTagEntities = $themeTagRepository->findBySlugs($tagSlugs);

			$entityManager = $this->doctrine->getManager();

			foreach ($apiTags as $apiTag) {
				if (isset($themeTagEntities[$apiTag['slug']])) {
					$themeTagEntity = $themeTagEntities[$apiTag['slug']];
				} else {
					$themeTagEntity = null;
				}

				$this->ingestTag($apiTag, $entityManager, $themeTagEntity);
			}

			$entityManager->flush();
			$entityManager->clear();
		}
	}

	/**
	 * Ingest a theme tag from the WordPress.org API into the database.
	 *
	 * @param array $tag The theme tag to ingest.
	 *
	 * @return void
	 */
	public function ingestTag(array $tag, ObjectManager $entityManager, ?ThemeTag $themeTagEntity = null): void
	{
		if ($themeTagEntity === null) {
			$themeTagEntity = new ThemeTag();
		}

		$themeTagEntity->setFromArray($tag);

		$entityManager->persist($themeTagEntity);
	}

	/**
	 * The method that ingests the theme stats from the WordPress.org API.
	 *
	 * @param array $apiThemes The theme stats from the API.
	 *
	 * @return void
	 */
	public function ingestStats(array $apiThemes)
	{
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

			$this->logger->info('Ingesting {count} theme stats.', [
				'count' => count($themeEntities),
			]);

			$entityManager = $this->doctrine->getManager();

			foreach ($apiThemes['themes'] as $apiTheme) {
				if (isset($themeEntities[$apiTheme['slug']])) {
					$themeEntity = $themeEntities[$apiTheme['slug']];
					$this->ingestStatsForTheme($apiTheme, $themeEntity, $entityManager);
				}
			}

			$entityManager->flush();
			$entityManager->clear();
		}
	}

	/**
	 * Ingest the stats for a theme from the WordPress.org API into the database.
	 *
	 * @param array $theme The theme stats to ingest.
	 * @param Theme $themeEntity The theme entity to ingest the stats for.
	 * @param ObjectManager $entityManager The entity manager to use.
	 *
	 * @return void
	 */
	public function ingestStatsForTheme(array $theme, Theme $themeEntity, ObjectManager $entityManager): void
	{
		$statsEntity = new ThemeStatSnapshot();

		$statsEntity->setTheme($themeEntity);
		$statsEntity->setFromArray($theme);

		/**
		 * The usage score of the theme.
		 */
		$freshnessFactor = ($theme['active_installs'] / $theme['downloaded']) * $theme['active_installs'];
		$statsEntity->setUsageScore($freshnessFactor);

		$entityManager->persist($statsEntity);
	}
}
