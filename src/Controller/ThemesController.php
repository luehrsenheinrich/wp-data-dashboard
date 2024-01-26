<?php

namespace App\Controller;

use App\ControllerFilter\ThemeFilter;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use App\Repository\ThemeStatSnapshotRepository;
use App\Repository\ThemeTagRepository;
use App\Service\NavigationService;
use App\Service\PageMetaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Omines\DataTablesBundle\Adapter\ArrayAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;

class ThemesController extends AbstractController
{
	public function __construct(
		private ThemeRepository $themeRepository,
		private ThemeStatSnapshotRepository $themeStatSnapshotRepository,
		private ThemeTagRepository $themeTagRepository,
		private NavigationService $navigationService,
		private PageMetaService $pageMetaService,
		private DataTableFactory $dataTableFactory,
	) {
	}

	#[Route('/themes', name: 'app_themes')]
	public function index(): Response
	{
		$themeStats = $this->themeRepository->getCurrentStats();
		$themeAuthorDiversityScore = $this->themeRepository->getAuthorDiversityScore();

		$this->pageMetaService->addTitlePart('WordPress Themes');

		return $this->render('themes/index.html.twig', [
			'total_theme_stat_snapshots' => $this->themeStatSnapshotRepository->getTotalCount(),
			'stats' => $themeStats,
			'ratings' => $this->themeRepository->getCurrentAverageRating(),
			'diversity' => $themeAuthorDiversityScore,
		]);
	}

	#[Route('/themes/details/{slug}', name: 'app_themes_single', requirements: ['slug' => '[a-z0-9-]+'])]
	public function single(string $slug): Response
	{
		// Retrieve the theme from the database.
		$theme = $this->themeRepository->getBySlug($slug);

		// If the theme does not exist, return a 404 response.
		if (!$theme) {
			throw $this->createNotFoundException();
		}

		$this->pageMetaService->addTitlePart($theme->getName());
		$this->pageMetaService->addTitlePart('WordPress Theme');

		$this->pageMetaService->setDescription(
			'Information and stats about the WordPress Theme '.$theme->getName().'. '.
			$theme->getDescription()
		);

		return $this->render('themes/single.html.twig', [
			'theme' => $this->themeRepository->getBySlug($slug),
		]);
	}

	#[Route('/themes/list/{page}', name: 'app_themes_list', requirements: ['page' => '\d+'])]
	public function list(Request $request, $page = 1): Response
	{
		$filter = new ThemeFilter();
		$filter->setPage($page);
		$filter->setPerPage(20);
		$filter->setOrderBy('downloaded');
		$filter->setOrder('DESC');

		$themes = $this->themeRepository->findAllWithFilter($filter);

		$this->pageMetaService->addTitlePart('WordPress Themes');

		if ($page > 1) {
			$this->pageMetaService->addTitlePart('Page '.$page);
		}

		$pagination = array(
			'current' => (int) $filter->getPage(),
			'total' => (int) ceil($themes->count() / $filter->getPerPage()),
			'range' => $this->navigationService->generatePaginationRange($filter->getPage(), ceil($themes->count() / $filter->getPerPage())),
		);

		$themeStats = $this->themeRepository->getCurrentStats();

		$table = $this->dataTableFactory->create()
			->add('name', TextColumn::class)
			->createAdapter(ORMAdapter::class, [
				'entity' => Theme::class,
			])
			->handleRequest($request);

		if ($table->isCallback()) {
			return $table->getResponse();
		}

		return $this->render('themes/list.html.twig', [
			'themes' => $themes,
			'pagination' => $pagination,
			'stats' => $themeStats,
			'datatable' => $table,
		]);
	}

	#[Route('/themes/stats/', name: 'app_themes_stats')]
	public function stats(): Response
	{

		$this->pageMetaService->addTitlePart('WordPress Theme Statistics');

		return $this->render('themes/stats.html.twig', [
			'all' => [
				'stats' => $this->themeRepository->getCurrentStats(),
				'ratings' => $this->themeRepository->getCurrentAverageRating(),
				'diversity' => $this->themeRepository->getAuthorDiversityScore(),
			],
			'woDefault' => [
				'stats' => $this->themeRepository->getCurrentStats(['wordpressdotorg']),
				'ratings' => $this->themeRepository->getCurrentAverageRating(['wordpressdotorg']),
				'diversity' => $this->themeRepository->getAuthorDiversityScore(['wordpressdotorg']),
			]
		]);
	}


	#[Route('/themes/tag/{themeTagSlug}/{page}', name: 'app_themes_tag', requirements: ['themeTagSlug' => '[a-z0-9-]+', 'page' => '\d+'])]
	public function tag(string $themeTagSlug, int $page = 1): Response
	{
		$themeTag = $this->themeTagRepository->findOneBy(['slug' => $themeTagSlug]);

		if (!$themeTag) {
			throw $this->createNotFoundException();
		}

		$filter = new ThemeFilter();
		$filter->setPage($page);
		$filter->setPerPage(20);
		$filter->setOrderBy('downloaded');
		$filter->setOrder('DESC');

		$themes = $this->themeRepository->findAllWithFilterByTag($filter, $themeTag);

		$this->pageMetaService->addTitlePart($themeTag->getName());
		$this->pageMetaService->addTitlePart('WordPress Themes');

		if ($page > 1) {
			$this->pageMetaService->addTitlePart('Page '.$page);
		}

		$pagination = array(
			'current' => (int) $filter->getPage(),
			'total' => (int) ceil($themes->count() / $filter->getPerPage()),
			'range' => $this->navigationService->generatePaginationRange($filter->getPage(), ceil($themes->count() / $filter->getPerPage())),
		);

		return $this->render('themes/tag.html.twig', [
			'themeTag' => $themeTag,
			'themes' => $themes,
			'pagination' => $pagination,
		]);
	}

	#[Route('/themes/tag/{themeTagSlug}/stats/', name: 'app_themes_tag_stats', requirements: ['themeTagSlug' => '[a-z0-9-]+'])]
	public function tagStats(string $themeTagSlug): Response
	{
		$themeTag = $this->themeTagRepository->findOneBy(['slug' => $themeTagSlug]);

		$this->pageMetaService->addTitlePart($themeTag->getName());
		$this->pageMetaService->addTitlePart('WordPress Theme Statistics');

		return $this->render('themes/tagStats.html.twig', [
			'themeTag' => $themeTag,
			'all' => [
				'stats' => $this->themeRepository->getCurrentStats(),
				'ratings' => $this->themeRepository->getCurrentAverageRating(),
				'diversity' => $this->themeRepository->getAuthorDiversityScore(),
			],
			'tag' => [
				'stats' => $this->themeRepository->getCurrentStats(array(), array($themeTag)),
				'ratings' => $this->themeRepository->getCurrentAverageRating(array(), array($themeTag)),
				'diversity' => $this->themeRepository->getAuthorDiversityScore(array(), array($themeTag)),
			],
			'woDefault' => [
				'stats' => $this->themeRepository->getCurrentStats(['wordpressdotorg'], array($themeTag)),
				'ratings' => $this->themeRepository->getCurrentAverageRating(['wordpressdotorg'], array($themeTag)),
				'diversity' => $this->themeRepository->getAuthorDiversityScore(['wordpressdotorg'], array($themeTag)),
			]
		]);
	}

	#[Route('/themes/tags/', name: 'app_themes_tags')]
	public function tags(): Response
	{
		$this->pageMetaService->addTitlePart('WordPress Theme Tags');

		return $this->render('themes/tags.html.twig', [
			'tags' => $this->themeTagRepository->findAll(),
		]);
	}
}
