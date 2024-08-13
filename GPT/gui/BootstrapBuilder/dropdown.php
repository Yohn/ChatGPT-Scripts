<?php

/**
 * Class BootstrapDropdown
 *
 * A class for rendering a Bootstrap dropdown component.
 */
class BootstrapDropdown extends BootstrapComponent {

	/**
	 * @var array The array containing structured data for the dropdown.
	 */
	private array $dropdownData;

	/**
	 * BootstrapDropdown constructor.
	 *
	 * @param array $dropdownData The structured data for the dropdown component.
	 */
	public function __construct(array $dropdownData) {
		$this->dropdownData = $dropdownData;
	}

	/**
	 * Render the dropdown component.
	 *
	 * @return string The HTML string of the rendered dropdown.
	 */
	public function render(): string {
		$html = '<div class="dropdown">';
		$html .= $this->renderToggle();
		$html .= $this->renderMenu();
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render the dropdown toggle button.
	 *
	 * @return string The HTML string of the rendered dropdown toggle button.
	 */
	private function renderToggle(): string {
		return '<a class="nav-link dropdown-toggle" href="#" id="' . $this->escape($this->dropdownData['id']) . '" role="button" data-bs-toggle="dropdown" aria-expanded="false">' . $this->escape($this->dropdownData['text']) . '</a>';
	}

	/**
	 * Render the dropdown menu.
	 *
	 * @return string The HTML string of the rendered dropdown menu.
	 */
	private function renderMenu(): string {
		$html = '<ul class="dropdown-menu" aria-labelledby="' . $this->escape($this->dropdownData['id']) . '">';

		foreach ($this->dropdownData['items'] as $item) {
			if (isset($item['divider']) && $item['divider']) {
				$html .= '<li><hr class="dropdown-divider"></li>';
			} elseif (isset($item['header']) && $item['header']) {
				$html .= '<li><h6 class="dropdown-header">' . $this->escape($item['text']) . '</h6></li>';
			} elseif (isset($item['disabled']) && $item['disabled']) {
				$html .= '<li><a class="dropdown-item disabled" href="#" tabindex="-1" aria-disabled="true">' . $this->escape($item['text']) . '</a></li>';
			} elseif (isset($item['form']) && $item['form']) {
				$html .= '<li>' . $item['form'] . '</li>'; // Assuming the form is provided as pre-rendered HTML
			} else {
				$html .= '<li><a class="dropdown-item" href="' . $this->escape($item['href']) . '">' . $this->escape($item['text']) . '</a></li>';
			}
		}

		$html .= '</ul>';

		return $html;
	}
}
