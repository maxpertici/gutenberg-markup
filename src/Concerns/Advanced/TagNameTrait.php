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
	protected string $tag_name = 'div';

	/**
	 * List of allowed HTML tag names.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $allowed_tag_names = array(
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
	 * @param string $tag_name The HTML tag name (div, header, main, section, article, aside, footer).
	 * @return self Returns the instance for method chaining.
	 */
	public function tagName( string $tag_name ): self {
		// Validate and sanitize tag name
		$tag_name = strtolower( trim( $tag_name ) );

		if ( ! in_array( $tag_name, $this->allowed_tag_names, true ) ) {
			$tag_name = 'div'; // Fallback to default
		}

		$this->tag_name = $tag_name;

		// Add tagName to block attributes if not default 'div'
		if ( 'div' !== $tag_name ) {
			$this->block_attributes['tagName'] = $tag_name;
		} else {
			// Remove tagName from attributes if set back to default
			unset( $this->block_attributes['tagName'] );
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
		return $this->tag_name;
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
		$this->wrapper = '<' . $this->tag_name . ' class="%classes%" %attributes%>%children%</' . $this->tag_name . '>';
	}

	/**
	 * Get the wrapper template with the current tag name.
	 *
	 * @since 1.0.0
	 *
	 * @return string The wrapper template.
	 */
	protected function getWrapperWithTag(): string {
		return '<' . $this->tag_name . ' class="%classes%" %attributes%>%children%</' . $this->tag_name . '>';
	}
}


