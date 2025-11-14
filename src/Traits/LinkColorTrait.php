<?php
/**
 * Link Color Trait
 *
 * Provides link color functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Traits
 */

namespace MaxPertici\GutenbergMarkup\Traits;

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
	public function link_color( string $color ): self {
		// Set the link color in block attributes
		$this->block_attributes['style']['elements']['link']['color']['text'] = "var:preset|color|{$color}";

		// Add the has-link-color class
		$this->add_class( 'has-link-color' );

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
	public function custom_link_color( string $color ): self {
		// Set the link color in block attributes
		$this->block_attributes['style']['elements']['link']['color']['text'] = $color;

		// Add the has-link-color class
		$this->add_class( 'has-link-color' );

		return $this;
	}
}

