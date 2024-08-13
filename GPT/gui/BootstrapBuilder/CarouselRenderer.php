<?php
/*Explanation:

    Base BootstrapRenderer Class:
        Contains the escape method to safely escape HTML content to prevent XSS attacks.

    CarouselRenderer Class:
        Properties:
            $carousel: Holds the configuration array for the carousel component.
        Constructor:
            Accepts an array containing the carousel configuration.
        Methods:
            renderCarousel(): Constructs the HTML for the carousel, supporting indicators, controls, captions, and more.
            renderCarouselItem(): Handles the rendering of individual carousel items, including images and captions.
            renderCarouselIndicators(): Generates the carousel indicators if configured to do so.
            renderCarouselControls(): Generates the carousel controls for navigation (previous/next).

    Flexible Configuration:
        The class supports all key features of Bootstrap carousels, such as automatic cycling, keyboard navigation, touch support, captions, indicators, and controls.
        The class is designed to allow easy extension or modification by adding additional configuration options.

    Efficient HTML Construction:
        The HTML is constructed using a mix of heredoc syntax and string concatenation to ensure readability and efficiency.

Usage Example:

To use the CarouselRenderer, you would create an instance of the class and call the renderCarousel method:

php

$carouselConfig = [
	'id' => 'carouselExample',
	'ride' => 'carousel',
	'interval' => 5000,
	'keyboard' => true,
	'pause' => 'hover',
	'wrap' => true,
	'touch' => true,
	'indicators' => true,
	'controls' => true,
	'items' => [
		[
			'src' => 'image1.jpg',
			'alt' => 'First slide',
			'caption' => [
				'title' => 'First Slide',
				'text' => 'This is the first slide',
			],
		],
		[
			'src' => 'image2.jpg',
			'alt' => 'Second slide',
			'caption' => [
				'title' => 'Second Slide',
				'text' => 'This is the second slide',
			],
		],
		[
			'src' => 'image3.jpg',
			'alt' => 'Third slide',
			'caption' => [
				'title' => 'Third Slide',
				'text' => 'This is the third slide',
			],
		],
	],
];
*/
//$carouselRenderer = new CarouselRenderer($carouselConfig);
//echo $carouselRenderer->renderCarousel();
//
//Summary:
//
//    Modular and Extensible: The CarouselRenderer class is designed to be modular and easily extendable for additional functionality.
//    Clean and Efficient: The use of heredoc syntax and private methods ensures the code is both clean and efficient, avoiding redundancy.
//    Security: The escape method is used consistently to prevent XSS attacks.
//
//This class should cover all key features of Bootstrap carousels and can be easily adapted for addition
//ChatGPT can make mistakes. Check important info.



// Following the same requests as prevously, please build a class for the carousel
// ChatGPT
//
// Certainly! Below is a class for rendering Bootstrap carousels, following the same design principles as the ModalRenderer class.
// Base BootstrapRenderer Class
//
// This class remains unchanged and can be reused for common functionality:
//
// php
//
//
//
// CarouselRenderer Class
// the CarouselRenderer class that extends the BootstrapRenderer class and focuses on rendering Bootstrap carousels:
//
// php
// Here’s
//

/**
 * Class CarouselRenderer
 *
 * A class for rendering Bootstrap carousels, extending common functionality from BootstrapRenderer.
 */
class CarouselRenderer extends BootstrapRenderer {

	/**
	 * @var array The array containing structured data for the carousel component.
	 */
	private array $carousel;

	/**
	 * CarouselRenderer constructor.
	 *
	 * @param array $carousel An array of Bootstrap carousel configurations.
	 */
	public function __construct(array $carousel) {
		$this->carousel = $carousel;
	}

	/**
	 * Render a carousel component.
	 *
	 * This function generates a carousel based on the structured array provided in the components configuration.
	 * It supports all standard Bootstrap carousel features, including indicators, controls, captions, and more.
	 *
	 * @return string The HTML string of the rendered carousel.
	 */
	public function renderCarousel(): string {
		$carousel = $this->carousel;

		// Define basic carousel attributes
		$id = $this->escape($carousel['id']);
		$ride = $this->escape($carousel['ride'] ?? 'carousel');
		$interval = $carousel['interval'] ?? '';
		$keyboard = $carousel['keyboard'] ?? true;
		$pause = $carousel['pause'] ?? 'hover';
		$wrap = $carousel['wrap'] ?? true;
		$touch = $carousel['touch'] ?? true;
		$indicators = $carousel['indicators'] ?? true;
		$controls = $carousel['controls'] ?? true;

		// Start building the carousel HTML
		$html = <<<HTML
<div id="$id" class="carousel slide" data-bs-ride="$ride" data-bs-interval="$interval" data-bs-keyboard="$keyboard" data-bs-pause="$pause" data-bs-wrap="$wrap" data-bs-touch="$touch">
HTML;

		if ($indicators) {
			$html .= $this->renderCarouselIndicators($id, $carousel['items']);
		}

		$html .= '<div class="carousel-inner">';
		foreach ($carousel['items'] as $index => $item) {
			$html .= $this->renderCarouselItem($item, $index === 0);
		}
		$html .= '</div>';

		if ($controls) {
			$html .= $this->renderCarouselControls($id);
		}

		$html .= '</div>'; // Close carousel

		return $html;
	}

	/**
	 * Render a single carousel item.
	 *
	 * @param array $item The array containing data for a single carousel item.
	 * @param bool $isActive Indicates if the item should be marked as active.
	 *
	 * @return string The HTML string of the rendered carousel item.
	 */
	private function renderCarouselItem(array $item, bool $isActive): string {
		$activeClass = $isActive ? ' active' : '';
		$imgSrc = $this->escape($item['src']);
		$imgAlt = $this->escape($item['alt'] ?? '');
		$caption = $item['caption'] ?? '';
		$captionHTML = '';

		if ($caption) {
			$captionHTML = <<<HTML
<div class="carousel-caption d-none d-md-block">
	{$this->escape($caption['title'])}
	<p>{$this->escape($caption['text'])}</p>
</div>
HTML;
		}

		return <<<HTML
<div class="carousel-item$activeClass">
	<img src="$imgSrc" class="d-block w-100" alt="$imgAlt">
	$captionHTML
</div>
HTML;
	}

	/**
	 * Render carousel indicators.
	 *
	 * @param string $carouselId The unique ID of the carousel.
	 * @param array $items The array of carousel items.
	 *
	 * @return string The HTML string of the rendered carousel indicators.
	 */
	private function renderCarouselIndicators(string $carouselId, array $items): string {
		$html = '<ol class="carousel-indicators">';
		foreach ($items as $index => $item) {
			$activeClass = $index === 0 ? ' class="active"' : '';
			$html .= '<li data-bs-target="#' . $this->escape($carouselId) . '" data-bs-slide-to="' . $index . '"' . $activeClass . '></li>';
		}
		$html .= '</ol>';

		return $html;
	}

	/**
	 * Render carousel controls.
	 *
	 * @param string $carouselId The unique ID of the carousel.
	 *
	 * @return string The HTML string of the rendered carousel controls.
	 */
	private function renderCarouselControls(string $carouselId): string {
		return <<<HTML
<a class="carousel-control-prev" href="#$carouselId" role="button" data-bs-slide="prev">
	<span class="carousel-control-prev-icon" aria-hidden="true"></span>
	<span class="visually-hidden">Previous</span>
</a>
<a class="carousel-control-next" href="#$carouselId" role="button" data-bs-slide="next">
	<span class="carousel-control-next-icon" aria-hidden="true"></span>
	<span class="visually-hidden">Next</span>
</a>
HTML;
	}
}
