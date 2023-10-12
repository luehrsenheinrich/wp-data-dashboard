<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\ThemeAuthor;
use App\Service\WpOrgApiCrawlService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomeController extends AbstractController
{
	public function __construct(
		private HttpClientInterface $client,
		private ManagerRegistry $doctrine,
		private WpOrgApiCrawlService $wpOrg,
	) {
	}

	#[Route('/', name: 'app_home')]
	public function index(): Response
	{
		return $this->render(
			'home/index.html.twig',
			[
				'controller_name' => 'HomeController',
			]
		);
	}

	#[Route('crawl_themes', name: 'app_crawl_themes')]
	public function crawlThemes(): Response
	{

		//$this->wpOrg->maybeCrawlThemeInfos();
		$this->wpOrg->maybeCrawlThemeTags();

		return $this->render(
			'home/index.html.twig',
			[
				'controller_name' => 'CrawlThemesController',
			]
		);
	}
}
