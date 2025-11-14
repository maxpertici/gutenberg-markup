<?php
/**
 * Text Decoration Trait
 *
 * Provides text decoration functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Traits
 */

namespace MaxPertici\GutenbergMarkup\Traits;

/**
 * Text Decoration Trait.
 *
 * Adds text-decoration support to Gutenberg blocks (none, underline, overline, line-through).
 *
 * @since 1.0.0
 */
trait TextDecorationTrait {

	/**
	 * Set the text decoration (none, underline, overline, line-through).
	 *
	 * @since 1.0.0
	 *
	 * @param string $decoration The text decoration value.
	 * @return self Returns the instance for method chaining.
	 */
	public function text_decoration( string $decoration ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . "text-decoration:{$decoration}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->block_attributes['style']['typography']['textDecoration'] = $decoration;

		return $this;
	}
}

