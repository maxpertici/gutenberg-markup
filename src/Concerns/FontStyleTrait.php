<?php
/**
 * Font Style Trait
 *
 * Provides font style functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Font Style Trait.
 *
 * Adds font-style support to Gutenberg blocks (normal, italic, oblique).
 *
 * @since 1.0.0
 */
trait FontStyleTrait {

	/**
	 * Set the font style (normal, italic, oblique).
	 *
	 * @since 1.0.0
	 *
	 * @param string $style The font style value.
	 * @return self Returns the instance for method chaining.
	 */
	public function fontStyle( string $style ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . "font-style:{$style}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->block_attributes['style']['typography']['fontStyle'] = $style;

		return $this;
	}
}

