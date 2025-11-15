<?php
/**
 * Custom Class Trait
 *
 * Provides custom CSS class functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Custom Class Trait.
 *
 * Adds support for custom CSS classes to Gutenberg blocks via the className attribute.
 * This corresponds to the "Additional CSS class(es)" field in the block editor.
 *
 * @since 1.0.0
 */
trait CustomClassTrait {

	/**
	 * Add custom CSS classes to the block.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class_name One or more CSS classes separated by spaces.
	 * @return self Returns the instance for method chaining.
	 */
	public function customClass( string $class_name ): self {
		// Set the className attribute
		$this->block_attributes['className'] = $class_name;

		// Add the classes to the wrapper
		$classes = explode( ' ', $class_name );
		foreach ( $classes as $class ) {
			$class = trim( $class );
			if ( ! empty( $class ) ) {
				$this->addClass( $class );
			}
		}

		return $this;
	}
}

