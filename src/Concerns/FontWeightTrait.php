<?php
/**
 * Font Weight Trait
 *
 * Provides font weight functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Font Weight Trait.
 *
 * Adds font-weight support to Gutenberg blocks (100-900 or normal, bold, etc.).
 *
 * @since 1.0.0
 */
trait FontWeightTrait {

	/**
	 * Set the font weight (100-900 or normal, bold, etc.).
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $weight The font weight value.
	 * @return self Returns the instance for method chaining.
	 */
	public function fontWeight( $weight ): self {
		$weight = (string) $weight;

		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . "font-weight:{$weight}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->block_attributes['style']['typography']['fontWeight'] = $weight;

		return $this;
	}
}

