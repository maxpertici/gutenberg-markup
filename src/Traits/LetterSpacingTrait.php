<?php
/**
 * Letter Spacing Trait
 *
 * Provides letter spacing functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Traits
 */

namespace MaxPertici\GutenbergMarkup\Traits;

/**
 * Letter Spacing Trait.
 *
 * Adds letter-spacing support to Gutenberg blocks.
 *
 * @since 1.0.0
 */
trait LetterSpacingTrait {

	/**
	 * Set the letter spacing.
	 *
	 * @since 1.0.0
	 *
	 * @param string $spacing The letter spacing value (e.g., '2em', '3px').
	 * @return self Returns the instance for method chaining.
	 */
	public function letter_spacing( string $spacing ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . "letter-spacing:{$spacing}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->block_attributes['style']['typography']['letterSpacing'] = $spacing;

		return $this;
	}
}

