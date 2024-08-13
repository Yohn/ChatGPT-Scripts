<?php

/*
Explanation:

    Base BootstrapRenderer Class:
        Contains the escape method, which is used to safely escape HTML content to prevent XSS vulnerabilities.
        This class can be extended by other component renderers (e.g., ModalRenderer, AccordionRenderer).

    ModalRenderer Class:
        Properties:
            $modal: Holds the configuration array for the modal component.
        Constructor:
            Accepts an array containing the modal configuration.
        Methods:
            renderModal(): Constructs the HTML for the modal, including all key features documented by Bootstrap.
            renderModalFooter(): Handles rendering of the modal's footer, supporting both string and array inputs.

    Heredoc Syntax:
        The HTML is built using the heredoc syntax for cleaner and more readable code.

Usage:

To render a modal, you would create an instance of ModalRenderer and call the renderModal method:


!! $modalConfig = [
!! 	'id' => 'exampleModal',
!! 	'aria_labelledby' => 'exampleModalLabel',
!! 	'header' => 'Modal title',
!! 	'body' => 'This is the body of the modal.',
!! 	'footer' => [
!! 		'<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>',
!! 		'<button type="button" class="btn btn-primary">Save changes</button>',
!! 	],
!! 	'fade' => true,
!! 	'centered' => true,
!! 	'size' => 'modal-lg',
!! ];

$modalRenderer = new ModalRenderer($modalConfig);
echo $modalRenderer->renderModal();

Summary:

    Modular Design: The ModalRenderer class is now focused solely on rendering modals, while the BootstrapRenderer base class can be extended for other Bootstrap components.
    Scalability: This design allows for easy extension and reuse of common functions across different component renderers.
    Optimization: The use of heredoc syntax and a clean class structure ensures that the code is both efficient and maintainable.
*/
/**
 * Class ModalRenderer
 *
 * A class for rendering Bootstrap modals, extending common functionality from BootstrapRenderer.
 */
class ModalRenderer extends BootstrapRenderer {

	/**
	 * @var array The array containing structured data for the modal component.
	 */
	private array $modal;

	/**
	 * ModalRenderer constructor.
	 *
	 * @param array $modal An array of Bootstrap modal configurations.
	 */
	public function __construct(array $modal) {
		$this->modal = $modal;
	}

	/**
	 * Render a modal component.
	 *
	 * This function generates a modal based on the structured array provided in the components configuration.
	 * It supports all standard Bootstrap modal features, including sizes, scrolling, centering, and more.
	 *
	 * @return string The HTML string of the rendered modal.
	 */
	public function renderModal(): string {
		$modal = $this->modal;

		// Define basic modal attributes
		$id = $this->escape($modal['id']);
		$labelledBy = $this->escape($modal['aria_labelledby']);
		$role = $this->escape($modal['role'] ?? 'dialog');
		$hidden = $this->escape($modal['aria_hidden'] ?? 'true');
		$fade = $modal['fade'] ?? true ? 'fade' : '';
		$dialogClass = $modal['dialog_class'] ?? '';
		$centered = $modal['centered'] ?? false ? 'modal-dialog-centered' : '';
		$scrollable = $modal['scrollable'] ?? false ? 'modal-dialog-scrollable' : '';
		$size = $modal['size'] ?? ''; // Accepts 'modal-sm', 'modal-lg', 'modal-xl'
		$fullScreen = $modal['fullscreen'] ?? ''; // Accepts 'modal-fullscreen', 'modal-fullscreen-sm-down', etc.

		// Start building the modal HTML
		return <<<HTML
<div class="modal $fade" id="$id" tabindex="-1" aria-labelledby="$labelledBy" aria-hidden="$hidden" role="$role">
	<div class="modal-dialog $dialogClass $centered $scrollable $size $fullScreen">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="$labelledBy">{$this->escape($modal['header'])}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				{$this->escape($modal['body'])}
			</div>
			<div class="modal-footer">
				{$this->renderModalFooter($modal['footer'])}
			</div>
		</div>
	</div>
</div>
HTML;
	}

	/**
	 * Render the footer of the modal.
	 *
	 * This function allows for a dynamic footer with multiple buttons or other elements.
	 *
	 * @param array|string $footer The content of the footer, either as a string or an array of elements.
	 * @return string The rendered footer HTML.
	 */
	private function renderModalFooter($footer): string {
		if (is_array($footer)) {
			$html = '';
			foreach ($footer as $element) {
				$html .= $this->escape($element);
			}
			return $html;
		}

		return $this->escape($footer);
	}
}