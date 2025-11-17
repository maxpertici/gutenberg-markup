<?php
/**
 * Text Decoration Trait
 *
 * Provides text decoration functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Typography
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Typography;

/**
 * Text Decoration Trait.
 *
 * Adds text-decoration support to Gutenberg blocks (none, underline, overline, line-through).
 *
 * @since 1.0.0
 */
trait TextDecorationTrait {

	/**
	 * Set the text decoration (none, underline, overline, line-through).
	 *
	 * @since 1.0.0
	 *
	 * @param string $decoration The text decoration value.
	 * @return self Returns the instance for method chaining.
	 */
	public function textDecoration( string $decoration ): self {
		// Get current inline style or initialize as empty string
		$current_style = $this->wrapperAttributes['style'] ?? '';
		
		// Add semicolon separator if there are existing styles
		if ( ! empty( $current_style ) ) {
			$current_style .= ';';
		}
		
		// Append the CSS property to wrapper attributes
		$this->wrapperAttributes['style'] = $current_style . "text-decoration:{$decoration}";
		
		// Also add to block attributes for Gutenberg's typography system
		$this->blockAttributes['style']['typography']['textDecoration'] = $decoration;

		return $this;
	}
}

