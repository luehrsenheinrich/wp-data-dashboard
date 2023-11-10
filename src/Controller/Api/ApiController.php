<?php

namespace App\Controller\Api;

use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Api')]
class ApiController extends AbstractController
{
	/**
	 * This route ensures proper 404 on non-existing routes in the api namespace.
	 */
	#[Route(path: '/{apiRouting}', name: 'api_notfound', priority: -1, defaults: ['apiRouting' => null], requirements: ['apiRouting' => '.+'])]
	public function index(): View
	{
		return View::create(null, JsonResponse::HTTP_NOT_FOUND);
	}
}
