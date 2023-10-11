<?php
/**
 * The class that helps us crawl the WordPress.org API.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\Theme;
use App\Entity\ThemeTag;
use App\Message\ThemeInfoCrawl;
use App\Options\ThemeCrawlerStateOption;
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

		if ($themeEntity === null) {
			$themeEntity = new Theme();
		}

		$themeEntity->setFromArray($theme);

		$entityManager->persist($themeEntity);
	}
}
