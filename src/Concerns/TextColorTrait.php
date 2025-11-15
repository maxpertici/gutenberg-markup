<?php
/**
 * Text Color Trait for Gutenberg Blocks.
 *
 * This trait provides functionality to apply text color styling to Gutenberg blocks.
 * It handles both inline styles and block attributes for proper color management.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Text Color Trait.
 *
 * Provides methods to apply text color styling to Gutenberg blocks.
 * This trait modifies both wrapper attributes (for inline styles) and
 * block attributes (for Gutenberg's color system).
 *
 * @since 1.0.0
 */
trait TextColorTrait {

	/**
	 * Apply text color using a preset color slug.
	 *
	 * This method uses WordPress theme color presets and generates the appropriate
	 * CSS classes like 'has-{color}-color' and 'has-text-color'.
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The color slug from theme preset (e.g., 'primary', 'pale-pink').
	 * @return self Returns the instance for method chaining.
	 */
	public function textColor( string $color ): self {
		// Set the textColor attribute for preset colors
		$this->block_attributes['textColor'] = $color;

		// Add the color class
		$this->addClass( "has-{$color}-color" );
		
		// Add the has-text-color class
		$this->addClass( 'has-text-color' );

		return $this;
	}

	/**
	 * Apply custom text color using a direct color value.
	 *
	 * This method adds text color styling using inline styles:
	 * 1. As an inline style on the wrapper element
	 * 2. As a block attribute for Gutenberg's color system
	 *
	 * The color parameter can be:
	 * - A hex color value (e.g., '#333333')
	 * - A CSS color name (e.g., 'red')
	 * - An RGB/RGBA value (e.g., 'rgb(255, 0, 0)')
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The text color value.
	 * @return self Returns the instance for method chaining.
	 */
	public function customTextColor( string $color ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapper_attributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the color style to wrapper attributes
		$this->wrapper_attributes['style'] = $current_style . 'color:' . $color;
		
		// Also add to block attributes for Gutenberg's color system
		// This ensures compatibility with the block editor's color palette
		$this->block_attributes['style']['color']['text'] = $color;

		// Add the has-text-color class
		$this->addClass( 'has-text-color' );

		return $this;
	}
}

