<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Entity\ThemeAuthor;
use App\Service\PageMetaService;
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
		private PageMetaService $pageMetaService,
	) {
	}

	#[Route('/', name: 'app_home')]
	public function index(): Response
	{

		$this->pageMetaService->addTitlePart('Unveil the Dynamics of WordPress Ecosystem');

		return $this->render(
			'home/index.html.twig',
			[
				'controller_name' => 'HomeController',
			]
		);
	}
}
