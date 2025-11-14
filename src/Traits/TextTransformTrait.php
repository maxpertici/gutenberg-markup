<?php
/**
 * Text Transform Trait
 *
 * Provides text transform functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Traits
 */

namespace MaxPertici\GutenbergMarkup\Traits;

/**
 * Text Transform Trait.
 *
 * Adds text-transform support to Gutenberg blocks (none, uppercase, lowercase, capitalize).
 *
 * @since 1.0.0
 */
trait TextTransformTrait {

	/**
	 * Set the text transform (none, uppercase, lowercase, capitalize).
	 *
	 * @since 1.0.0
	 *
	 * @param string $transform The text transform value.
	 * @return self Returns the instance for method chaining.
	 */
	public function text_transform( string $transform ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . "text-transform:{$transform}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->block_attributes['style']['typography']['textTransform'] = $transform;

		return $this;
	}
}

