<?php
/**
 * The class that helps us crawl the WordPress.org API.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Theme;
use App\Entity\ThemeAuthor;
use App\Entity\ThemeTag;
use App\Message\ThemeInfoCrawl;
use App\Options\ThemeCrawlerStateOption;
use App\Repository\ThemeAuthorRepository;
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
	 * The datetime offset after which we need to crawl again.
	 */
	protected $dateTimeOffset = '-5 minutes';

	/**
	 * An array of ThemeAuthor entities indexed by their user_nicename.
	 *
	 * @var ThemeAuthor[]
	 */
	protected $themeAuthors = [];

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
		$this->perPage = intval($this->params->get('app.wp_crawl_per_page'));
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
		];

		$this->logger->info('Requesting themes from WordPress.org API.', [
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
		$crawlDateTimeOffset = new \DateTimeImmutable($this->dateTimeOffset);

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
			 * @var ThemeRepository $themeRepository
			 */
			$themeRepository = $this->doctrine->getRepository(Theme::class);
			$themeEntities = $themeRepository->findThemesBySlugs($themeSlugs);

			/**
			 * @var ThemeAuthorRepository $themeAuthorRepository
			 */
			$themeAuthorRepository = $this->doctrine->getRepository(ThemeAuthor::class);
			$this->themeAuthors = $themeAuthorRepository->findByUserNicenames($nicenames);

			$entityManager = $this->doctrine->getManager();

			foreach ($apiThemes['themes'] as $apiTheme) {
				if (isset($themeEntities[$apiTheme['slug']])) {
					$themeEntity = $themeEntities[$apiTheme['slug']];
				} else {
					$themeEntity = null;
				}

				$this->ingestTheme($apiTheme, $entityManager, $themeEntity);
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
	public function ingestTheme(array $theme, ObjectManager $entityManager, ?Theme $themeEntity = null): void
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

		if (isset($this->themeAuthors[$theme['author']['user_nicename']]) && $this->themeAuthors[$theme['author']['user_nicename']] instanceof ThemeAuthor) {
			$themeAuthor = $this->themeAuthors[$theme['author']['user_nicename']];
		} else {
			$themeAuthor = $themeEntity->getAuthor();
			$this->themeAuthors[$theme['author']['user_nicename']] = $themeAuthor;
		}

		$themeAuthor->setFromArray($theme['author']);
		unset($theme['author']);

		$themeEntity->setFromArray($theme);
		$themeEntity->setAuthor($themeAuthor);

		$entityManager->persist($themeEntity);
	}
}
