<?php
/**
 * A class that provides a service that handles our navigation menus.
 */

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function classNames;

class NavigationService
{

	/**
	 * The request.
	 *
	 * @var Request
	 */
	private $request;

	public function __construct(
        protected RequestStack $requestStack,
		protected UrlGeneratorInterface $router,
	){
	}

	/**
	 * The header menu.
	 * An array of arrays with the following keys:
	 * - label: The label of the menu item.
	 * - route: The route of the menu item.
	 * - url: The url of the menu item.
	 *
	 * If the route is set, the url is ignored.
	 *
	 * @var array
	 */
	protected $headerMenu = [
		[
			'label' => 'Home',
			'route' => 'app_home',
			'url' => false,
		],
		[
			'label' => 'Themes',
			'route' => 'app_themes',
			'url' => false,
		],
	];

	/**
	 * The footer menu.
	 * An array of arrays with the following keys:
	 * - label: The label of the menu item.
	 * - route: The route of the menu item.
	 * - url: The url of the menu item.
	 *
	 * If the route is set, the url is ignored.
	 *
	 * @var array
	 */
	protected $footerMenu = [
		[
			'label' => 'Home',
			'route' => 'app_home',
			'url' => false,
		],
		[
			'label' => 'Legal Notes',
			'route' => false,
			'url' => 'https://www.luehrsen-heinrich.de/impressum',
		],
		[
			'label' => 'Privacy Policy',
			'route' => false,
			'url' => 'https://www.luehrsen-heinrich.de/datenschutz',
		],
	];

	/**
	 * Get the header menu as html.
	 *
	 * @return string The header menu as html.
	 */
	public function getHeaderMenu(): string
	{
		// Analyze the request.
		$this->request = $this->requestStack->getCurrentRequest();

		$args = [
			'classNames' => [
				'nav-header',
				'navbar-nav',
			]
		];
		return $this->menuToHtml($this->headerMenu, $args);
	}

	/**
	 * Get the footer menu as html.
	 *
	 * @return string The footer menu as html.
	 */
	public function getFooterMenu(): string
	{
		// Analyze the request.
		$this->request = $this->requestStack->getCurrentRequest();

		$args = [
			'classNames' => [
				'nav-footer',
				'd-flex',
				'justify-content-center',
			],
		];
		return $this->menuToHtml($this->footerMenu, $args);
	}

	/**
	 * Transform a menu array into html.
	 * We follow the Boostrap 5 menu structure.
	 *
	 * @param array $menu The menu array.
	 * @param array $args The arguments.
	 *
	 * @return string The menu as html.
	 *
	 * @see https://getbootstrap.com/docs/5.2/components/navs-tabs/
	 */
	private function menuToHtml(array $menu, array $args = []): string
	{
		$navClasses = classNames(
			$args['classNames'] ?? [],
			array(
				'nav',
			)
		);

		$html = '';
		$html .= '<ul class="'.$navClasses.'">';

		foreach ($menu as $item) {

			// If the route is set, we use the route.
			if (isset($item['route']) && $item['route']) {
				$item['url'] = $this->router->generate($item['route']);
			}

			// Generate the menu item classes.
			$itemClasses = classNames(
				array(
					'nav-item',
				)
			);

			// Generate the menu link classes.
			$linkClasses = classNames(
				array(
					'nav-link',
					'active' => $this->request->attributes->get('_route') === $item['route'],
				)
			);

			$html .= '<li class="'.$itemClasses.'">';
				$html .= '<a class="'.$linkClasses.'" href="'.$item['url'].'">'.$item['label'].'</a>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

}
