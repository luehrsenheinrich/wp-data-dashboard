<?php

namespace App\Controller;

use App\Repository\ThemeRepository;
use App\Repository\ThemeStatSnapshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemesController extends AbstractController
{
	public function __construct(
		private ThemeRepository $themeRepository,
		private ThemeStatSnapshotRepository $themeStatSnapshotRepository
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

	#[Route('/themes/{slug}', name: 'app_themes_single')]
	public function single(string $slug): Response
	{
		// Retrieve the theme from the database.
		$theme = $this->themeRepository->getNewestTheme($slug);

		// If the theme does not exist, return a 404 response.
		if (!$theme) {
			throw $this->createNotFoundException();
		}

		return $this->render('themes/single.html.twig', [
			'theme' => $this->themeRepository->getNewestTheme($slug),
		]);
	}
}
