<?php

namespace App\Controller;

use App\ControllerFilter\ThemeFilter;
use App\Repository\ThemeRepository;
use App\Repository\ThemeStatSnapshotRepository;
use App\Service\NavigationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemesController extends AbstractController
{
	public function __construct(
		private ThemeRepository $themeRepository,
		private ThemeStatSnapshotRepository $themeStatSnapshotRepository,
		private NavigationService $navigationService,
	) {
	}

	#[Route('/themes', name: 'app_themes')]
	public function index(): Response
	{
		return $this->render('themes/index.html.twig', [
			'total_themes' => $this->themeRepository->getTotalThemes(),
			'total_theme_stat_snapshots' => $this->themeStatSnapshotRepository->getTotalCount(),
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

	#[Route('/themes/list', name: 'app_themes_list')]
	#[Route('/themes/list/page/{page}', name: 'app_themes_list_paginated', requirements: ['page' => '\d+'])]
	public function list($page = 1): Response
	{
		$filter = new ThemeFilter();
		$filter->setPage($page);

		$themes = $this->themeRepository->findAllWithFilter($filter);

		$pagination = array(
			'current' => (int) $filter->getPage(),
			'total' => (int) ceil($themes->count() / $filter->getPerPage()),
			'range' => $this->navigationService->generatePaginationRange($filter->getPage(), ceil($themes->count() / $filter->getPerPage())),
		);

		return $this->render('themes/list.html.twig', [
			'themes' => $themes,
			'pagination' => $pagination,
		]);
	}
}
