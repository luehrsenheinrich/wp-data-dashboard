<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\ThemeAuthor;
use App\Service\PageMetaService;
use App\Service\WpOrgApiCrawlService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HomeController extends AbstractController
{
	public function __construct(
		private HttpClientInterface $client,
		private ManagerRegistry $doctrine,
		private WpOrgApiCrawlService $wpOrg,
		private PageMetaService $pageMetaService,
	) {
	}

	#[Route('/', name: 'app_home')]
	public function index(): Response
	{

		$this->pageMetaService->addTitlePart('Unveil the Dynamics of WordPress Ecosystem');
		$this->pageMetaService->setDescription('WP Data Dashboard is your centralized hub for exploring, analyzing, and visualizing data across the WordPress landscape. Venture into various modules and discover actionable insights from a wealth of data, curated from diverse sources within the WordPress ecosystem.');

		return $this->render(
			'home/index.html.twig',
			[
				'controller_name' => 'HomeController',
			]
		);
	}
}
