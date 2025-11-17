<?php
/**
 * Link Color Trait
 *
 * Provides link color functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Color
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Color;

/**
 * Link Color Trait.
 *
 * Adds link color support to Gutenberg blocks using the elements.link.color system.
 * This allows styling of links within block content.
 *
 * @since 1.0.0
 */
trait LinkColorTrait {

	/**
	 * Set link color using a preset color slug.
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The color slug (e.g., 'primary', 'pale-pink').
	 * @return self Returns the instance for method chaining.
	 */
	public function linkColor( string $color ): self {
		// Set the link color in block attributes
		$this->blockAttributes['style']['elements']['link']['color']['text'] = "var:preset|color|{$color}";

		// Add the has-link-color class
		$this->addClass( 'has-link-color' );

		return $this;
	}

	/**
	 * Set link color using a custom color value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $color The color value (hex, rgb, etc.).
	 * @return self Returns the instance for method chaining.
	 */
	public function customLinkColor( string $color ): self {
		// Set the link color in block attributes
		$this->blockAttributes['style']['elements']['link']['color']['text'] = $color;

		// Add the has-link-color class
		$this->addClass( 'has-link-color' );

		return $this;
	}
}

