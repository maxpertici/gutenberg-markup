<?php
/**
 * Font Size Trait
 *
 * Provides font size functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Font Size Trait.
 *
 * Adds font size support to Gutenberg blocks using theme preset sizes.
 * Generates appropriate classes like 'has-{size}-font-size'.
 *
 * @since 1.0.0
 */
trait FontSizeTrait {

	/**
	 * Set the font size using a preset slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $size The font size slug (e.g., 'small', 'medium', 'large', 'x-large').
	 * @return self Returns the instance for method chaining.
	 */
	public function fontSize( string $size ): self {
		// Set the fontSize attribute
		$this->block_attributes['fontSize'] = $size;

		// Add the font size class
		$this->addClass( "has-{$size}-font-size" );

		return $this;
	}

	/**
	 * Set a custom font size value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $size The font size value (e.g., '16px', '1.5rem', '2em').
	 * @return self Returns the instance for method chaining.
	 */
	public function customFontSize( string $size ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the font-size style to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . 'font-size:' . $size;
		
		// Also add to block attributes for Gutenberg's typography system
		$this->block_attributes['style']['typography']['fontSize'] = $size;

		return $this;
	}
}

