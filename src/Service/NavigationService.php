<?php
/**
 * A class that provides a service that handles our navigation menus.
 */

declare(strict_types=1);

namespace App\Service;

class NavigationService
{
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
	 * Get the header menu as html.
	 */
	public function getHeaderMenu(): string
	{
		$args = [];

		return $this->menuToHtml($this->headerMenu, $args);
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
	public function menuToHtml(array $menu, array $args = []): string
	{
		$html = '';
		$html .= '<ul class="nav">';

		foreach ($menu as $item) {
			$html .= '<li class="nav-item">';
			$html .= '<a class="nav-link" href="'.$item['url'].'">'.$item['label'].'</a>';
			$html .= '</li>';
		}

		$html .= '</ul>';

		return $html;
	}

}
