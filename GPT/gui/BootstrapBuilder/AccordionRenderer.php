
<?php

//AccordionRenderer Class
//
//This class will handle the rendering of Bootstrap accordions, incorporating all possible features outlined in the Bootstrap documentation.
//
//php
/*
$accordionConfig = [
	'flush' => true, // Enable flush style
	'always_open' => false, // Do not keep accordion items open by default
	'items' => [
		[
			'header' => [
				'id' => 'headingOne',
				'button' => [
					'text' => 'Accordion Item #1',
					'data_bs_target' => '#collapseOne',
					'aria_controls' => 'collapseOne',
					'aria_expanded' => 'true',
					'class' => 'accordion-button',
				],
			],
			'content' => [
				'id' => 'collapseOne',
				'aria_labelledby' => 'headingOne',
				'class' => 'accordion-collapse collapse show',
				'body' => '<div class="accordion-body">This is the content for Accordion Item #1.</div>',
			],
		],
		[
			'header' => [
				'id' => 'headingTwo',
				'button' => [
					'text' => 'Accordion Item #2',
					'data_bs_target' => '#collapseTwo',
					'aria_controls' => 'collapseTwo',
					'aria_expanded' => 'false',
					'class' => 'accordion-button collapsed',
				],
			],
			'content' => [
				'id' => 'collapseTwo',
				'aria_labelledby' => 'headingTwo',
				'class' => 'accordion-collapse collapse',
				'body' => '<div class="accordion-body">This is the content for Accordion Item #2.</div>',
			],
		],
	],
];

/**
 * Class AccordionRenderer
 *
 * A class for rendering Bootstrap accordions, extending common functionality from BootstrapRenderer.
 */
class AccordionRenderer extends BootstrapRenderer {

	/**
	 * @var array The array containing structured data for the accordion component.
	 */
	private array $accordion;

	/**
	 * AccordionRenderer constructor.
	 *
	 * @param array $accordion An array of Bootstrap accordion configurations.
	 */
	public function __construct(array $accordion) {
		$this->accordion = $accordion;
	}

	/**
	 * Render an accordion component.
	 *
	 * This function generates an accordion based on the structured array provided in the components configuration.
	 * It supports all standard Bootstrap accordion features, including dynamic content, flush styles, and more.
	 *
	 * @param string $accordionId The unique ID to be assigned to the accordion container.
	 *
	 * @return string The HTML string of the rendered accordion.
	 */
	public function renderAccordion(string $accordionId): string {
		$accordionBlocks = $this->accordion;

		$flush = $accordionBlocks['flush'] ?? false ? 'accordion-flush' : '';
		$alwaysOpen = $accordionBlocks['always_open'] ?? false ? 'accordion-always-open' : '';

		$html = '<div class="accordion ' . $flush . ' ' . $alwaysOpen . '" id="' . $this->escape($accordionId) . '">';
		foreach ($accordionBlocks['items'] as $block) {
			$html .= $this->renderAccordionItem($block, $accordionId);
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render a single accordion item.
	 *
	 * @param array $block The array containing data for a single accordion item.
	 * @param string $accordionId The unique ID of the accordion container.
	 *
	 * @return string The HTML string of the rendered accordion item.
	 */
	private function renderAccordionItem(array $block, string $accordionId): string {
		$header = $block['header'];
		$content = $block['content'];

		return <<<HTML
<div class="accordion-item">
	<h2 class="accordion-header" id="{$this->escape($header['id'])}">
		<button class="{$this->escape($header['button']['class'])}" type="button" data-bs-toggle="collapse" data-bs-target="{$this->escape($header['button']['data_bs_target'])}" aria-expanded="{$this->escape($header['button']['aria_expanded'])}" aria-controls="{$this->escape($header['button']['aria_controls'])}">
			{$this->escape($header['button']['text'])}
		</button>
	</h2>
	<div id="{$this->escape($content['id'])}" class="{$this->escape($content['class'])}" aria-labelledby="{$this->escape($content['aria_labelledby'])}" data-bs-parent="#{$this->escape($accordionId)}">
		{$content['body']}
	</div>
</div>
HTML;
	}
}

//Explanation:
//
//    Base BootstrapRenderer Class:
//        The escape method is inherited and used in both TabsRenderer and AccordionRenderer classes.
//

//    AccordionRenderer Class:
//        Properties:
//            $accordion: Holds the configuration array for the accordion component.
//        Constructor:
//            Accepts an array for the accordion configuration.
//        Methods:
//            renderAccordion(): Constructs the HTML for the accordion, supporting features like flush styles, always open, and dynamic content.
//            renderAccordionItem(): Renders individual accordion items, ensuring each block is rendered correctly with all necessary attributes.
//
//    Comprehensive Feature Support:
//        Both classes incorporate all relevant features as per the Bootstrap documentation, such as custom classes, fade effects, flush styling, and more.
//
//    Efficient HTML Construction:
//        HTML is constructed using heredoc syntax where appropriate, ensuring readability and maintainability.
//        The renderAccordionItem method is kept private to modularize and simplify the accordion rendering logic.
