<?php
/**
 * Text Color Trait for Gutenberg Blocks.
 *
 * This trait provides functionality to apply text color styling to Gutenberg blocks.
 * It handles both inline styles and block attributes for proper color management.
 *
 * @package MaxPertici\GutenbergMarkup\Traits
 */

namespace MaxPertici\GutenbergMarkup\Traits;

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
	 * Apply text color to the block.
	 *
	 * This method adds text color styling in two ways:
	 * 1. As an inline style on the wrapper element
	 * 2. As a block attribute for Gutenberg's color system
	 *
	 * The color parameter can be either:
	 * - A hex color value (e.g., '#333333')
	 * - A CSS color name (e.g., 'red')
	 * - A WordPress color slug (e.g., 'primary')
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The text color (hex value, CSS color name, or slug).
	 * @return self Returns the instance for method chaining.
	 */
	public function text_color( string $color ): self {
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

		return $this;
	}
}

