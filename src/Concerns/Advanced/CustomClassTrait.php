<?php
/**
 * Custom Class Trait
 *
 * Provides custom CSS class functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Advanced
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Advanced;

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
	 * @param string $className One or more CSS classes separated by spaces.
	 * @return self Returns the instance for method chaining.
	 */
	public function customClass( string $className ): self {
		// Set the className attribute
		$this->blockAttributes['className'] = $className;

		// Add the classes to the wrapper
		$classes = explode( ' ', $className );
		foreach ( $classes as $class ) {
			$class = trim( $class );
			if ( ! empty( $class ) ) {
				$this->addClass( $class );
			}
		}

		return $this;
	}
}

