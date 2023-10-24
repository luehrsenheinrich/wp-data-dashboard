<?php

namespace App\Controller;

use App\ControllerFilter\ThemeFilter;
use App\Repository\ThemeRepository;
use App\Repository\ThemeStatSnapshotRepository;
use App\Repository\ThemeTagRepository;
use App\Service\NavigationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemesController extends AbstractController
{
	public function __construct(
		private ThemeRepository $themeRepository,
		private ThemeStatSnapshotRepository $themeStatSnapshotRepository,
		private ThemeTagRepository $themeTagRepository,
		private NavigationService $navigationService,
	) {
	}

	#[Route('/themes', name: 'app_themes')]
	public function index(): Response
	{
		$themeStats = $this->themeRepository->getCurrentStats();
		$themeAuthorDiversityScore = $this->themeRepository->getAuthorDiversityScore();

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

		$theme->getThemeStatSnapthots()->last();

		return $this->render('themes/single.html.twig', [
			'theme' => $this->themeRepository->getBySlug($slug),
		]);
	}

	#[Route('/themes/list/{page}', name: 'app_themes_list', requirements: ['page' => '\d+'])]
	public function list($page = 1): Response
	{
		$filter = new ThemeFilter();
		$filter->setPage($page);
		$filter->setPerPage(20);
		$filter->setSort(['-usage_score']);

		$themes = $this->themeRepository->findAllWithFilter($filter);

		$pagination = array(
			'current' => (int) $filter->getPage(),
			'total' => (int) ceil($themes->count() / $filter->getPerPage()),
			'range' => $this->navigationService->generatePaginationRange($filter->getPage(), ceil($themes->count() / $filter->getPerPage())),
		);

		$themeStats = $this->themeRepository->getCurrentStats();

		return $this->render('themes/list.html.twig', [
			'themes' => $themes,
			'pagination' => $pagination,
			'stats' => $themeStats,
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
		$filter->setSort(['-usage_score']);

		$themes = $this->themeRepository->findAllWithFilterByTag($filter, $themeTag);

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
}
