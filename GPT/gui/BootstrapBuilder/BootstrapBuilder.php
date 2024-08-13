<?php

/**
 * Class BootstrapRenderer
 *
 * A base class for rendering various Bootstrap components.
 */
class BootstrapRenderer {

	/**
	 * Escape HTML content to prevent XSS.
	 *
	 * @param string $content The content to be escaped.
	 * @return string The escaped content.
	 */
	protected function escape(string $content): string {
		return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
	}
}