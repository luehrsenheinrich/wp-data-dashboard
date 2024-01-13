<?php
/**
 * The controller that handles theme tags related API requests.
 */

declare(strict_types=1);

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Response;
use App\Dto\ThemeTagDto;
use App\Dto\Transformer\ThemeTagDtoTransformer;
use App\Repository\ThemeTagRepository;

#[OA\Tag(name:"Theme Tags")]
#[Rest\Route("/theme-tags")]
class ThemeTagsController extends AbstractLHRestController
{
	public function __construct(
		private ThemeTagRepository $themeTagRepository,
		private ThemeTagDtoTransformer $themeTagDtoTransformer,
	) {
	}

	/**
	 * Get theme tags.
	 * Useful to get an overview of the available theme tags.
	 *
	 * @return View
	 */
	#[REST\Get('/', name: 'api_theme_tags_get')]
	#[REST\View(serializerGroups: ['read'])]
	#[OA\Response(
		response: 200,
		description: "All theme tags.",
		content:  new OA\JsonContent(
			type: 'array',
			items: new OA\Items(
				ref: new Model(
					type: ThemeTagDto::class,
				)
			)
		)
	)]
	public function getThemeTagsAction(): View
	{
		/**
		 * Query the database for the ThemeTags.
		 *
		 * @var ThemeTag[] $themeTags
		 */
		$themeTags = $this->themeTagRepository->findAll();
		/**
		 * Transform the entities into DTOs.
		 *
		 * @var ThemeTagDto[] $dtos
		 */
		$dtos = $this->themeTagDtoTransformer->transformFromObjects($themeTags);

		/**
		 * Return the View.
		 */
		return View::create($dtos, Response::HTTP_OK);
	}
}
