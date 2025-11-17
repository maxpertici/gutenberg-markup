<?php
/**
 * TagName Trait
 *
 * Provides HTML tag name functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Advanced
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Advanced;

/**
 * TagName Trait.
 *
 * Adds HTML tag name support to Gutenberg blocks.
 * This allows blocks to use semantic HTML5 elements (header, main, section, article, aside, footer).
 *
 * @since 1.0.0
 */
trait TagNameTrait {

	/**
	 * The HTML tag name for the block wrapper.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $tagName = 'div';

	/**
	 * List of allowed HTML tag names.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $allowedTagNames = array(
		'div',
		'header',
		'main',
		'section',
		'article',
		'aside',
		'footer',
	);

	/**
	 * Set the HTML tag name for the block wrapper.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tagName The HTML tag name (div, header, main, section, article, aside, footer).
	 * @return self Returns the instance for method chaining.
	 */
	public function tagName( string $tagName ): self {
		// Validate and sanitize tag name
		$tagName = strtolower( trim( $tagName ) );

		if ( ! in_array( $tagName, $this->allowedTagNames, true ) ) {
			$tagName = 'div'; // Fallback to default
		}

		$this->tagName = $tagName;

		// Add tagName to block attributes if not default 'div'
		if ( 'div' !== $tagName ) {
			$this->blockAttributes['tagName'] = $tagName;
		} else {
			// Remove tagName from attributes if set back to default
			unset( $this->blockAttributes['tagName'] );
		}

		// Update the wrapper with the new tag name
		$this->updateWrapperTag();

		return $this;
	}

	/**
	 * Get the current HTML tag name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The current tag name.
	 */
	public function getTagName(): string {
		return $this->tagName;
	}

	/**
	 * Update the wrapper template with the current tag name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function updateWrapperTag(): void {
		// Update wrapper with current tag name
		$this->wrapper = '<' . $this->tagName . ' class="%classes%" %attributes%>%children%</' . $this->tagName . '>';
	}

	/**
	 * Get the wrapper template with the current tag name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The wrapper template.
	 */
	protected function getWrapperWithTag(): string {
		return '<' . $this->tagName . ' class="%classes%" %attributes%>%children%</' . $this->tagName . '>';
	}
}


