<?php
class BootstrapBuilder {
	private $components = [];

	/**
	 * Converts an array of attributes into a string for HTML.
	 * 
	 * @param array $attributes Key-value pairs of attributes.
	 * @return string The attributes as an HTML string.
	 */
	private function attributesToString($attributes) {
		$string = '';
		foreach ($attributes as $key => $value) {
			$string .= "$key='$value' ";
		}
		return trim($string);
	}

	/**
	 * Merges default classes with additional custom classes.
	 * 
	 * @param string $defaultClasses Default Bootstrap classes.
	 * @param string $additionalClasses User-provided custom classes.
	 * @return string Merged classes string.
	 */
	private function mergeClasses($defaultClasses, $additionalClasses) {
		return trim("$defaultClasses $additionalClasses");
	}

	/**
	 * Adds an accordion component.
	 * 
	 * @param string $id The ID of the accordion.
	 * @param array $items The items of the accordion, each containing a header and body.
	 * @param array $attributes Additional attributes for the accordion element.
	 * @param string $additionalClasses Custom classes for the accordion.
	 * @return $this
	 */
	public function accordion($id, $items = [], $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('accordion', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'id' => $id]));
		$content = '';
		foreach ($items as $key => $item) {
			$content .= "
				<div class='accordion-item'>
					<h2 class='accordion-header' id='heading$key'>
						<button class='accordion-button' type='button' data-bs-toggle='collapse' data-bs-target='#collapse$key' aria-expanded='true' aria-controls='collapse$key'>
							{$item['header']}
						</button>
					</h2>
					<div id='collapse$key' class='accordion-collapse collapse' aria-labelledby='heading$key' data-bs-parent='#$id'>
						<div class='accordion-body'>{$item['body']}</div>
					</div>
				</div>";
		}
		$this->components[] = "<div $attrString>$content</div>";
		return $this;
	}

	/**
	 * Adds a badge component.
	 * 
	 * @param string $content The text inside the badge.
	 * @param string $type The type of badge (e.g., 'badge-primary').
	 * @param array $attributes Additional attributes for the badge element.
	 * @param string $additionalClasses Custom classes for the badge.
	 * @return $this
	 */
	public function badge($content = '', $type = 'badge-primary', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses("badge $type", $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes]));
		$this->components[] = "<span $attrString>$content</span>";
		return $this;
	}

	/**
	 * Adds a breadcrumb component.
	 * 
	 * @param array $items The breadcrumb items, each containing 'text' and optional 'link'.
	 * @param array $attributes Additional attributes for the breadcrumb element.
	 * @param string $additionalClasses Custom classes for the breadcrumb.
	 * @return $this
	 */
	public function breadcrumb($items = [], $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('breadcrumb', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes]));
		$content = '';
		foreach ($items as $item) {
			if (isset($item['link'])) {
				$content .= "<li class='breadcrumb-item'><a href='{$item['link']}'>{$item['text']}</a></li>";
			} else {
				$content .= "<li class='breadcrumb-item active' aria-current='page'>{$item['text']}</li>";
			}
		}
		$this->components[] = "<nav aria-label='breadcrumb'><ol $attrString>$content</ol></nav>";
		return $this;
	}

	/**
	 * Adds a button group component.
	 * 
	 * @param array $buttons The buttons, each containing 'text' and 'type' (e.g., 'btn-primary').
	 * @param array $attributes Additional attributes for the button group element.
	 * @param string $additionalClasses Custom classes for the button group.
	 * @return $this
	 */
	public function buttonGroup($buttons = [], $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('btn-group', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes]));
		$content = '';
		foreach ($buttons as $button) {
			$content .= "<button type='button' class='btn {$button['type']}'>{$button['text']}</button>";
		}
		$this->components[] = "<div $attrString>$content</div>";
		return $this;
	}

	/**
	 * Adds a carousel component.
	 * 
	 * @param string $id The ID of the carousel.
	 * @param array $items The carousel items, each containing 'image', 'alt', 'title', and 'caption'.
	 * @param array $attributes Additional attributes for the carousel element.
	 * @param string $additionalClasses Custom classes for the carousel.
	 * @return $this
	 */
	public function carousel($id, $items = [], $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('carousel slide', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'id' => $id, 'data-bs-ride' => 'carousel']));
		$indicators = '';
		$inner = '';
		foreach ($items as $index => $item) {
			$activeClass = $index === 0 ? 'active' : '';
			$indicators .= "<button type='button' data-bs-target='#$id' data-bs-slide-to='$index' class='$activeClass' aria-current='true'></button>";
			$inner .= "
				<div class='carousel-item $activeClass'>
					<img src='{$item['image']}' class='d-block w-100' alt='{$item['alt']}'>
					<div class='carousel-caption d-none d-md-block'>
						<h5>{$item['title']}</h5>
						<p>{$item['caption']}</p>
					</div>
				</div>";
		}
		$this->components[] = "
			<div $attrString>
				<div class='carousel-indicators'>$indicators</div>
				<div class='carousel-inner'>$inner</div>
				<button class='carousel-control-prev' type='button' data-bs-target='#$id' data-bs-slide='prev'>
					<span class='carousel-control-prev-icon' aria-hidden='true'></span>
					<span class='visually-hidden'>Previous</span>
				</button>
				<button class='carousel-control-next' type='button' data-bs-target='#$id' data-bs-slide='next'>
					<span class='carousel-control-next-icon' aria-hidden='true'></span>
					<span class='visually-hidden'>Next</span>
				</button>
			</div>";
		return $this;
	}

	/**
	 * Adds a collapse component.
	 * 
	 * @param string $id The ID of the collapse element.
	 * @param string $content The content inside the collapse element.
	 * @param array $attributes Additional attributes for the collapse element.
	 * @param string $additionalClasses Custom classes for the collapse.
	 * @return $this
	 */
	public function collapse($id, $content = '', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('collapse', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'id' => $id]));
		$this->components[] = "<div $attrString>$content</div>";
		return $this;
	}

	/**
	 * Adds a dropdown component.
	 * 
	 * @param string $buttonText The text of the dropdown button.
	 * @param array $menuItems The menu items, each containing 'text' and 'link'.
	 * @param array $attributes Additional attributes for the dropdown button element.
	 * @param string $additionalClasses Custom classes for the dropdown button.
	 * @return $this
	 */
	public function dropdown($buttonText, $menuItems = [], $attributes = [], $additionalClasses = '') {
		$buttonClasses = $this->mergeClasses('btn dropdown-toggle', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $buttonClasses, 'data-bs-toggle' => 'dropdown', 'aria-expanded' => 'false']));
		$menu = '';
		foreach ($menuItems as $item) {
			$menu .= "<li><a class='dropdown-item' href='{$item['link']}'>{$item['text']}</a></li>";
		}
		$this->components[] = "
			<div class='dropdown'>
				<button $attrString>$buttonText</button>
				<ul class='dropdown-menu'>$menu</ul>
			</div>";
		return $this;
	}

	/**
	 * Adds a list group component.
	 * 
	 * @param array $items The list group items, each containing 'text' and optionally 'active' or 'disabled' states.
	 * @param array $attributes Additional attributes for the list group element.
	 * @param string $additionalClasses Custom classes for the list group.
	 * @return $this
	 */
	public function listGroup($items = [], $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('list-group', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes]));
		$content = '';
		foreach ($items as $item) {
			$itemClasses = 'list-group-item';
			if (isset($item['active']) && $item['active']) {
				$itemClasses .= ' active';
			}
			if (isset($item['disabled']) && $item['disabled']) {
				$itemClasses .= ' disabled';
			}
			$content .= "<li class='$itemClasses'>{$item['text']}</li>";
		}
		$this->components[] = "<ul $attrString>$content</ul>";
		return $this;
	}

	/**
	 * Adds a modal component.
	 * 
	 * @param string $id The ID of the modal.
	 * @param string $title The title of the modal.
	 * @param string $body The body content of the modal.
	 * @param array $attributes Additional attributes for the modal element.
	 * @param string $additionalClasses Custom classes for the modal.
	 * @return $this
	 */
	public function modal($id, $title = '', $body = '', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('modal fade', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'id' => $id, 'tabindex' => '-1', 'aria-hidden' => 'true']));
		$headerHtml = $title ? "<div class='modal-header'><h5 class='modal-title'>$title</h5><button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button></div>" : '';
		$this->components[] = "
			<div $attrString>
				<div class='modal-dialog'>
					<div class='modal-content'>
						$headerHtml
						<div class='modal-body'>$body</div>
					</div>
				</div>
			</div>";
		return $this;
	}

	/**
	 * Adds a navbar component.
	 * 
	 * @param array $items The navbar items, each containing 'text' and 'link', and optionally 'active'.
	 * @param string $brand The brand text of the navbar.
	 * @param array $attributes Additional attributes for the navbar element.
	 * @param string $additionalClasses Custom classes for the navbar.
	 * @return $this
	 */
	public function navbar($items = [], $brand = '', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('navbar navbar-expand-lg', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes]));
		$brandHtml = $brand ? "<a class='navbar-brand' href='#'>$brand</a>" : '';
		$content = '';
		foreach ($items as $item) {
			$activeClass = isset($item['active']) && $item['active'] ? 'active' : '';
			$content .= "<li class='nav-item'><a class='nav-link $activeClass' href='{$item['link']}'>{$item['text']}</a></li>";
		}
		$this->components[] = "
			<nav $attrString>
				<div class='container-fluid'>
					$brandHtml
					<button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
						<span class='navbar-toggler-icon'></span>
					</button>
					<div class='collapse navbar-collapse' id='navbarNav'>
						<ul class='navbar-nav'>
							$content
						</ul>
					</div>
				</div>
			</nav>";
		return $this;
	}

	/**
	 * Adds a navs or tabs component.
	 * 
	 * @param array $items The nav items, each containing 'text' and 'link', and optionally 'active'.
	 * @param bool $tabs If true, uses the 'nav-tabs' class; otherwise, uses the 'nav' class.
	 * @param array $attributes Additional attributes for the nav element.
	 * @param string $additionalClasses Custom classes for the nav.
	 * @return $this
	 */
	public function navs($items = [], $tabs = false, $attributes = [], $additionalClasses = '') {
		$navClass = $tabs ? 'nav-tabs' : 'nav';
		$classes = $this->mergeClasses($navClass, $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes]));
		$content = '';
		foreach ($items as $item) {
			$activeClass = isset($item['active']) && $item['active'] ? 'active' : '';
			$content .= "<li class='nav-item'><a class='nav-link $activeClass' href='{$item['link']}'>{$item['text']}</a></li>";
		}
		$this->components[] = "<ul $attrString>$content</ul>";
		return $this;
	}

	/**
	 * Adds an offcanvas component.
	 * 
	 * @param string $id The ID of the offcanvas element.
	 * @param string $title The title of the offcanvas element.
	 * @param string $body The body content of the offcanvas element.
	 * @param array $attributes Additional attributes for the offcanvas element.
	 * @param string $additionalClasses Custom classes for the offcanvas element.
	 * @return $this
	 */
	public function offcanvas($id, $title = '', $body = '', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('offcanvas offcanvas-start', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'id' => $id, 'tabindex' => '-1', 'aria-labelledby' => "$id-label"]));
		$headerHtml = $title ? "<div class='offcanvas-header'><h5 class='offcanvas-title' id='$id-label'>$title</h5><button type='button' class='btn-close' data-bs-dismiss='offcanvas' aria-label='Close'></button></div>" : '';
		$this->components[] = "
			<div $attrString>
				$headerHtml
				<div class='offcanvas-body'>
					$body
				</div>
			</div>";
		return $this;
	}

	/**
	 * Adds a pagination component.
	 * 
	 * @param array $pages The pagination items, each containing 'text', 'link', and optionally 'active' or 'disabled'.
	 * @param array $attributes Additional attributes for the pagination element.
	 * @param string $additionalClasses Custom classes for the pagination.
	 * @return $this
	 */
	public function pagination($pages = [], $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('pagination', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes]));
		$content = '';
		foreach ($pages as $page) {
			$itemClasses = 'page-item';
			if (isset($page['active']) && $page['active']) {
				$itemClasses .= ' active';
			}
			if (isset($page['disabled']) && $page['disabled']) {
				$itemClasses .= ' disabled';
			}
			$content .= "<li class='$itemClasses'><a class='page-link' href='{$page['link']}'>{$page['text']}</a></li>";
		}
		$this->components[] = "<ul $attrString>$content</ul>";
		return $this;
	}

	/**
	 * Adds a progress bar component.
	 * 
	 * @param int $value The current value of the progress bar.
	 * @param int $max The maximum value of the progress bar.
	 * @param array $attributes Additional attributes for the progress bar element.
	 * @param string $additionalClasses Custom classes for the progress bar.
	 * @return $this
	 */
	public function progress($value, $max = 100, $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('progress-bar', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'role' => 'progressbar', 'style' => "width: $value%;", 'aria-valuenow' => $value, 'aria-valuemin' => 0, 'aria-valuemax' => $max]));
		$this->components[] = "<div class='progress'><div $attrString></div></div>";
		return $this;
	}

	/**
	 * Adds a spinner component.
	 * 
	 * @param string $type The type of spinner ('border' or 'grow').
	 * @param string $color The color of the spinner (e.g., 'text-primary').
	 * @param array $attributes Additional attributes for the spinner element.
	 * @param string $additionalClasses Custom classes for the spinner.
	 * @return $this
	 */
	public function spinner($type = 'border', $color = 'text-primary', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses("spinner-$type $color", $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'role' => 'status']));
		$this->components[] = "<div $attrString><span class='visually-hidden'>Loading...</span></div>";
		return $this;
	}

	/**
	 * Adds a toast component.
	 * 
	 * @param string $body The body content of the toast.
	 * @param string $title The title of the toast.
	 * @param array $attributes Additional attributes for the toast element.
	 * @param string $additionalClasses Custom classes for the toast.
	 * @return $this
	 */
	public function toast($body = '', $title = '', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('toast', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'role' => 'alert', 'aria-live' => 'assertive', 'aria-atomic' => 'true']));
		$headerHtml = $title ? "<div class='toast-header'><strong class='me-auto'>$title</strong><button type='button' class='btn-close' data-bs-dismiss='toast' aria-label='Close'></button></div>" : '';
		$this->components[] = "<div $attrString>$headerHtml<div class='toast-body'>$body</div></div>";
		return $this;
	}

	/**
	 * Adds a tooltip component.
	 * 
	 * @param string $text The text of the tooltip.
	 * @param string $placement The placement of the tooltip ('top', 'bottom', 'left', 'right').
	 * @param string $target The ID of the element that triggers the tooltip.
	 * @param array $attributes Additional attributes for the tooltip element.
	 * @param string $additionalClasses Custom classes for the tooltip.
	 * @return $this
	 */
	public function tooltip($text = '', $placement = 'top', $target = '', $attributes = [], $additionalClasses = '') {
		$classes = $this->mergeClasses('tooltip', $additionalClasses);
		$attrString = $this->attributesToString(array_merge($attributes, ['class' => $classes, 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => $placement, 'title' => $text, 'id' => $target]));
		$this->components[] = "<div $attrString>$text</div>";
		return $this;
	}

	/**
	 * Renders all components that have been added.
	 * 
	 * @return string The final HTML output.
	 */
	public function render() {
		return implode("\n", $this->components);
	}

	/**
	 * Clears the component list, resetting the builder.
	 * 
	 * @return $this
	 */
	public function clear() {
		$this->components = [];
		return $this;
	}
}
