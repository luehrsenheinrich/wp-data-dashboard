<?php
/**
 * The controller that handles themes related API requests.
 */

declare(strict_types=1);

namespace App\Controller\Api;

use App\ControllerFilter\ThemeFilter;
use App\Dto\ThemeDto;
use App\Dto\Transformer\ThemeDtoTransformer;
use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use OpenApi\Attributes as OA;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\Serializer\Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;

#[OA\Tag(name:"Themes")]
#[Rest\Route("/themes")]
class ThemesController extends AbstractLHRestController
{
	public function __construct(
		private ThemeRepository $themeRepository,
		private ThemeDtoTransformer $themeDtoTransformer,
	) {
	}

	/**
	 * Get the themes.
	 *
	 * @param ThemeFilter $filter The filter.
	 *
	 * @return View
	 */
	#[REST\Get('/', name: 'api_themes_get')]
	#[REST\View(serializerGroups: ['read'])]
	#[OA\Parameter(
		in: 'query',
		name: 'query',
		schema: new OA\Schema(
			ref: new Model(
				type: ThemeFilter::class,
			),
		),
	)]
	#[OA\Response(
		response: 200,
		description: "All themes the current user is allowed to see.",
		content:  new OA\JsonContent(
			type: 'array',
			items: new OA\Items(
				ref: new Model(
					type: ThemeDto::class,
				)
			)
		)
	)]
	public function getThemesAction(
		#[MapQueryString] ThemeFilter $filter = new ThemeFilter()
	): View {
		/**
		 * Query the database for the Themes.
		 *
		 * @var Paginator<Theme> $themes
		 */
		$themes = $this->themeRepository->findAllWithFilter($filter);

		/**
		 * Transform the entities into DTOs.
		 *
		 * @var ThemeDto[] $dtos
		 */
		$dtos = $this->themeDtoTransformer->transformFromObjects($themes);

		/**
		 * The pagination headers.
		 *
		 * @var array
		 */
		$headers = array(
			'X-CURRENT-PAGE' => $filter->getPage(),
			'X-PER-PAGE' => $filter->getPerPage(),
			'X-TOTAL-COUNT' => $themes->count(),
		);

		/**
		 * Return the View.
		 */
		return View::create($dtos, Response::HTTP_OK, $headers);
	}

	/**
	 * Get a single theme.
	 *
	 * @param string $slug The theme slug.
	 *
	 * @return View
	 */
	#[REST\Get('/{slug}', name: 'api_themes_get_single')]
	#[REST\View(serializerGroups: ['read', 'read:theme'])]
	#[OA\Parameter(
		in: 'path',
		name: 'slug',
		description: 'The theme slug.',
		required: true,
		schema: new OA\Schema(
			type: 'string',
			example: 'twenty-twenty-two',
		),
	)]
	#[OA\Response(
		response: 200,
		description: "The theme.",
		content:  new OA\JsonContent(
			ref: new Model(
				type: ThemeDto::class,
			)
		)
	)]
	public function getThemeAction(string $slug): View
	{
		/**
		 * Query the database for the Theme.
		 *
		 * @var Theme|null $theme
		 */
		$theme = $this->themeRepository->getBySlug($slug);

		/**
		 * If the theme does not exist, return a 404 response.
		 */
		if (!$theme) {
			throw $this->createNotFoundException();
		}

		/**
		 * Transform the entity into a DTO.
		 *
		 * @var ThemeDto $dto
		 */
		$dto = $this->themeDtoTransformer->transformFromObject($theme);

		/**
		 * Return the View.
		 */
		return View::create($dto, Response::HTTP_OK);
	}
}
