<?php
/*
$navbarData = [
	'class' => 'navbar-expand-lg navbar-light bg-light',
	'collapse_id' => 'navbarNav',
	'brand' => [
		'href' => '/',
		'text' => 'MyBrand',
	],
	'links' => [
		[
			'href' => '/home',
			'text' => 'Home',
			'class' => 'active',
		],
		[
			'dropdown' => true,
			'id' => 'navbarDropdown',
			'text' => 'Dropdown',
			'items' => [
				[
					'href' => '/action1',
					'text' => 'Action 1',
				],
				[
					'href' => '/action2',
					'text' => 'Action 2',
				],
				[
					'divider' => true,
				],
				[
					'href' => '/something-else',
					'text' => 'Something else here',
				],
				[
					'header' => true,
					'text' => 'Dropdown Header',
				],
				[
					'disabled' => true,
					'text' => 'Disabled Item',
				],
				[
					'form' => '<form class="px-4 py-3"><div class="mb-3"><label for="exampleDropdownFormEmail1" class="form-label">Email address</label><input type="email" class="form-control" id="exampleDropdownFormEmail1" placeholder="email@example.com"></div><div class="mb-3"><label for="exampleDropdownFormPassword1" class="form-label">Password</label><input type="password" class="form-control" id="exampleDropdownFormPassword1" placeholder="Password"></div><button type="submit" class="btn btn-primary">Sign in</button></form>',
				],
			],
		],
		[
			'href' => '/about',
			'text' => 'About',
		],
	],
];

$navbar = new BootstrapNavbar($navbarData);
echo $navbar->render();

$navbar = new BootstrapNavbar($navbarData);
echo $navbar->render();
*/


/**
 * Class BootstrapNavbar
 *
 * A class for rendering a Bootstrap navbar component, extending from BootstrapComponent.
 */
class BootstrapNavbar extends BootstrapComponent {

	/**
	 * @var array The array containing structured data for the navbar.
	 */
	private array $navbarData;

	/**
	 * BootstrapNavbar constructor.
	 *
	 * @param array $navbarData The structured data for the navbar component.
	 */
	public function __construct(array $navbarData) {
		$this->navbarData = $navbarData;
	}

	/**
	 * Render the navbar component.
	 *
	 * @return string The HTML string of the rendered navbar.
	 */
	public function render(): string {
		$html = '<nav class="navbar ' . $this->escape($this->navbarData['class'] ?? '') . '">';
		$html .= '<div class="container-fluid">';
		$html .= $this->renderBrand();
		$html .= $this->renderToggler();
		$html .= '<div class="collapse navbar-collapse" id="' . $this->escape($this->navbarData['collapse_id']) . '">';
		$html .= $this->renderLinks($this->navbarData['links'] ?? []);
		$html .= '</div>';
		$html .= '</div></nav>';

		return $html;
	}

	/**
	 * Render the brand section of the navbar.
	 *
	 * @return string The HTML string of the rendered brand section.
	 */
	private function renderBrand(): string {
		if (!isset($this->navbarData['brand'])) {
			return '';
		}

		$brand = $this->navbarData['brand'];
		return '<a class="navbar-brand" href="' . $this->escape($brand['href']) . '">' . $this->escape($brand['text']) . '</a>';
	}

	/**
	 * Render the toggler button of the navbar.
	 *
	 * @return string The HTML string of the rendered toggler button.
	 */
	private function renderToggler(): string {
		return '<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#' . $this->escape($this->navbarData['collapse_id']) . '" aria-controls="' . $this->escape($this->navbarData['collapse_id']) . '" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>';
	}

	/**
	 * Render the navbar links and dropdowns.
	 *
	 * @param array $links The array of links and dropdowns.
	 *
	 * @return string The HTML string of the rendered navbar links and dropdowns.
	 */
	private function renderLinks(array $links): string {
		$html = '<ul class="navbar-nav me-auto mb-2 mb-lg-0">';
		foreach ($links as $link) {
			if (isset($link['dropdown'])) {
				$html .= $this->renderDropdown($link);
			} else {
				$html .= $this->renderLink($link);
			}
		}
		$html .= '</ul>';

		return $html;
	}

	/**
	 * Render a single navbar link.
	 *
	 * @param array $link The array containing data for a single link.
	 *
	 * @return string The HTML string of the rendered navbar link.
	 */
	private function renderLink(array $link): string {
		return '<li class="nav-item"><a class="nav-link ' . $this->escape($link['class'] ?? '') . '" href="' . $this->escape($link['href']) . '">' . $this->escape($link['text']) . '</a></li>';
	}

	/**
	 * Render a dropdown in the navbar using the BootstrapDropdown class.
	 *
	 * @param array $dropdown The array containing data for the dropdown.
	 *
	 * @return string The HTML string of the rendered dropdown.
	 */
	private function renderDropdown(array $dropdown): string {
		$dropdownComponent = new BootstrapDropdown($dropdown);
		return '<li class="nav-item dropdown">' . $dropdownComponent->render() . '</li>';
	}
}
