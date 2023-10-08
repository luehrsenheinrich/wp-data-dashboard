<?php
/**
 * The class that helps us crawl the WordPress.org API.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\ThemeSnapshot;
use App\Entity\ThemeTag;
use App\Options\ThemeCrawlerStateOption;
use App\Repository\ThemeSnapshotRepository;
use App\Repository\ThemeTagRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
	 * Request the themes from the WordPress.org API.
	 *
	 * @param int $page The page to request.
	 * @param int $perPage The number of themes to request.
	 *
	 * @return array The themes from the API.
	 */
	public function requestThemes(int $page = 1, int $perPage = 5): array
	{
		$params = [
			'action' => 'query_themes',
			'request[page]' => $page,
			'request[per_page]' => $perPage,
			'request[fields][active_installs]' => true,
			'request[fields][tags]' => true,
		];

		$this->logger->info('Requesting themes from WordPress.org API.', [
			'params' => $params,
		]);

		return $this->request($this->themeEndpoint, $params);
	}

	/**
	 * Crawl and ingest the themes from the WordPress.org API.
	 * This method does not have properties, as it handles the crawl state
	 * with an option from the database.
	 *
	 * @return array The themes object from the API.
	 */
	public function crawlThemes(): ?array
	{
		/**
		 * @var ThemeCrawlerStateOption $crawlState
		 */
		$crawlState = $this->optionsService->get('theme_crawler_state');

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
		if ($crawlState->getStatus() === 'finished' && $crawlState->getStartDateTime() < $crawlDateTimeOffset) {
			$this->logger->info('Starting a new theme crawl.');
			$crawlState->setStatus('running');
			$crawlState->setCurrentPage(1);
			$crawlState->setStartDateTime(new \DateTimeImmutable());
		} elseif ($crawlState->getStatus() === 'finished') {
			return null;
		}

		/**
		 * If no crawl date time is set, set it to now.
		 */
		if (!$crawlState->getStartDateTime()) {
			$crawlState->setStartDateTime(new \DateTimeImmutable());
		}

		/**
		 * The themes from the API.
		 */
		$themes = $this->requestThemes($crawlState->getCurrentPage(), $this->perPage);

		/**
		 * Check if themes are available in the response.
		 */
		if (!empty($themes['themes'])) {
			$entityManager = $this->doctrine->getManager();

			foreach ($themes['themes'] as $theme) {
				$this->ingestTheme($theme, $entityManager);
			}

			$entityManager->flush();
			$entityManager->clear();

			$this->logger->info('Ingested {count} themes from WordPress.org API on page {page} of {pages}.', [
				'count' => count($themes['themes']),
				'page' => $crawlState->getCurrentPage(),
				'pages' => $themes['info']['pages'],
			]);


			/**
			 * @var ThemeSnapshotRepository $themeSnapshotRepository
			 */
			$themeSnapshotRepository = $this->doctrine->getRepository(ThemeSnapshot::class);

			/**
			 * Load the theme entites that we just ingested.
			 */
			$themeEntities = $themeSnapshotRepository->findNewestThemeSnapshotBySlugs(array_column($themes['themes'], 'slug'));
			$tagEntities = $this->prepareTags($themes['themes'], $entityManager);

			/**
			 * An array of theme entities, keyed by slug.
			 *
			 * @var array<string, ThemeTag> $tagEntitesArray
			 */
			$tagEntitiesArray = [];
			foreach ($tagEntities as $tagEntity) {
				$tagEntitiesArray[$tagEntity->getSlug()] = $tagEntity;
			}

			/**
			 * An array of theme entities, keyed by slug.
			 *
			 * @var array<string, ThemeSnapshot> $themeEntitiesArray
			 */
			$themeEntitiesArray = [];
			foreach ($themeEntities as $themeEntity) {
				$themeEntitiesArray[$themeEntity->getSlug()] = $themeEntity;
			}

			foreach ($themes['themes'] as $theme) {
				if (empty($theme['tags'])) {
					continue;
				}

				$themeEntity = $themeEntitiesArray[$theme['slug']];

				foreach ($theme['tags'] as $tag) {
					$tagSlug = (new AsciiSlugger())->slug($tag)->lower()->toString();
					$themeEntitiesArray[$theme['slug']]
						->addTag($tagEntitiesArray[$tagSlug]);
				}

				$entityManager->persist($themeEntitiesArray[$theme['slug']]);
			}

			$entityManager->flush();
			$entityManager->clear();
		}

		/**
		 * Check if we have reached the last page.
		 */
		if ($themes['info']['pages'] <= $themes['info']['page']) {
			$crawlState->setStatus('finished');
		} else {
			$crawlState->setCurrentPage($crawlState->getCurrentPage() + 1);
		}

		$this->optionsService->set($crawlState);

		return $themes;
	}

	/**
	 * Ingest a theme from the WordPress.org API into the database.
	 *
	 * @param array $theme The theme to ingest.
	 * @param EntityManagerInterface $entityManager The entity manager to use.
	 *
	 * @return void
	 */
	public function ingestTheme(array $theme, ObjectManager $entityManager): void
	{
		/*
		 * Fix some type issues.
		 */

		if (!empty($theme['version'])) {
			$theme['version'] = (string) $theme['version'];
		}

		$themeSnapshot = new ThemeSnapshot();

		$themeSnapshot->setFromArray($theme);

		$entityManager->persist($themeSnapshot);
	}

	/**
	 * Prepare the tags for ingestion.
	 *
	 * @param array $themes The themes to prepare the tags for.
	 * @param ObjectManager $entityManager The entity manager to use.
	 *
	 * @return array
	 */
	public function prepareTags(array $themes, ObjectManager $entityManager): array
	{
		$tags = [];

		foreach ($themes as $theme) {
			if (!empty($theme['tags'])) {
				foreach ($theme['tags'] as $tag) {
					$tagSlug = (new AsciiSlugger())->slug($tag)->lower()->toString();
					$tags[$tagSlug] = $tag;
				}
			}
		}

		$tagEntities = $this->themeTagRepository->findBySlugs(array_keys($tags));

		foreach ($tagEntities as $tagEntity) {
			unset($tags[$tagEntity->getSlug()]);
		}

		foreach ($tags as $name => $tag) {
			$tagEntity = new ThemeTag();
			$tagEntity->setName($tag);
			$tagEntity->setSlug($name);

			$entityManager->persist($tagEntity);
			$tagEntities[] = $tagEntity;
		}

		return $tagEntities;
	}
}
