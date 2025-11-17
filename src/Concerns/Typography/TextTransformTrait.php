<?php
/**
 * Text Transform Trait
 *
 * Provides text transform functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Typography
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Typography;

/**
 * Text Transform Trait.
 *
 * Adds text-transform support to Gutenberg blocks (none, uppercase, lowercase, capitalize).
 *
 * @since 1.0.0
 */
trait TextTransformTrait {

	/**
	 * Set the text transform (none, uppercase, lowercase, capitalize).
	 *
	 * @since 1.0.0
	 *
	 * @param string $transform The text transform value.
	 * @return self Returns the instance for method chaining.
	 */
	public function textTransform( string $transform ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapperAttributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapperAttributes['style'] = $current_style . "text-transform:{$transform}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->blockAttributes['style']['typography']['textTransform'] = $transform;

		return $this;
	}
}

