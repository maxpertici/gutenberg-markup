<?php
/**
 * Line Height Trait
 *
 * Provides line height functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Line Height Trait.
 *
 * Adds line-height support to Gutenberg blocks.
 *
 * @since 1.0.0
 */
trait LineHeightTrait {

	/**
	 * Set the line height.
	 *
	 * @since 1.0.0
	 *
	 * @param string $height The line height value (e.g., '1.5', '24px').
	 * @return self Returns the instance for method chaining.
	 */
	public function lineHeight( string $height ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . "line-height:{$height}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->block_attributes['style']['typography']['lineHeight'] = $height;

		return $this;
	}
}

