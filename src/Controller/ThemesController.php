<?php

namespace App\Controller;

use App\Repository\ThemeSnapshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ThemesController extends AbstractController
{
	public function __construct(
		private ThemeSnapshotRepository $themeSnapshotRepository
	) {
	}

	#[Route('/themes', name: 'app_themes')]
	public function index(): Response
	{
		return $this->render('themes/index.html.twig', [
			'total_snapshots' => $this->themeSnapshotRepository->getTotalSnapshots(),
			'total_themes' => $this->themeSnapshotRepository->getTotalThemes(),
		]);
	}

	#[Route('/themes/{slug}', name: 'app_themes_single')]
	public function single(string $slug): Response
	{
		return $this->render('themes/single.html.twig', [
			'theme' => $this->themeSnapshotRepository->getNewestThemeSnapshot($slug),
		]);
	}
}
