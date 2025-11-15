<?php
/**
 * Background Color Trait
 *
 * Provides background color functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Background Color Trait.
 *
 * Adds background color support to Gutenberg blocks.
 *
 * @since 1.0.0
 */
trait BackgroundColorTrait {

	/**
	 * Apply background color using a preset color slug.
	 *
	 * This method uses WordPress theme color presets and generates the appropriate
	 * CSS classes like 'has-{color}-background-color' and 'has-background'.
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The color slug from theme preset (e.g., 'primary', 'secondary').
	 * @return self Returns the instance for method chaining.
	 */
	public function backgroundColor( string $color ): self {
		// Set the backgroundColor attribute for preset colors
		$this->block_attributes['backgroundColor'] = $color;

		// Add the background color class
		$this->addClass( "has-{$color}-background-color" );
		
		// Add the has-background class
		$this->addClass( 'has-background' );

		return $this;
	}

	/**
	 * Apply custom background color using a direct color value.
	 *
	 * This method adds background color styling using inline styles.
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The background color (hex value, RGB, etc.).
	 * @return self Returns the instance for method chaining.
	 */
	public function customBackgroundColor( string $color ): self {
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

		// Add the has-background class
		$this->addClass( 'has-background' );

		// Return self to allow method chaining
		return $this;
	}
}

