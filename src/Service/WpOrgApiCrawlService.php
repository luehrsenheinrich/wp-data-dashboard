<?php
/**
 * The class that helps us crawl the WordPress.org API.
 */

declare(strict_types=1);

namespace App\Service;

use App\Entity\ThemeSnapshot;
use App\Options\ThemeCrawlerStateOption;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
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
	) {
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
		];

		return $this->request($this->themeEndpoint, $params);
	}

	/**
	 * Crawl and ingest the themes from the WordPress.org API.
	 * This method does not have properties, as it handles the crawl state
	 * with an option from the database.
	 *
	 * @return void
	 */
	public function crawlThemes(): void
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
			$crawlState->setStatus('running');
			$crawlState->setCurrentPage(1);
			$crawlState->setStartDateTime(new \DateTimeImmutable());
		}

		/**
		 * If no crawl date time is set, set it to now.
		 */
		if (!$crawlState->getStartDateTime()) {
			$crawlState->setStartDateTime(new \DateTimeImmutable());
		}

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
		$themeSnapshot = new ThemeSnapshot();

		$themeSnapshot->setFromArray($theme);

		$entityManager->persist($themeSnapshot);
	}
}
