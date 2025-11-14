<?php
/**
 * Background Color Trait
 *
 * Provides background color functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Traits
 */

namespace MaxPertici\GutenbergMarkup\Traits;

/**
 * Background Color Trait.
 *
 * Adds background color support to Gutenberg blocks.
 *
 * @since 1.0.0
 */
trait BackgroundColorTrait {

	/**
	 * Apply background color to the block.
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The background color (hex value or slug).
	 * @return self Returns the instance for method chaining.
	 */
	public function background_color( string $color ): self {
		// Get the current inline style attribute if it exists
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// If there's already a style, add a semicolon separator
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the background-color CSS property to the wrapper's inline style
		$this->wrapper_attributes['style'] = $current_style . 'background-color:' . $color;
		
		// Store the background color in the block attributes for Gutenberg compatibility
		// This follows the WordPress block style structure for color management
		$this->block_attributes['style']['color']['background'] = $color;

		// Return self to allow method chaining
		return $this;
	}
}

