<?php
/**
 * Text Align Trait
 *
 * Provides text alignment functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Typography
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Typography;

/**
 * Text Align Trait.
 *
 * Adds text alignment support to Gutenberg blocks.
 * Generates appropriate classes like 'has-text-align-{value}'.
 *
 * @since 1.0.0
 */
trait TextAlignTrait {

	/**
	 * List of allowed text alignment values.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $allowedTextAlignments = array(
		'left',
		'center',
		'right',
	);

	/**
	 * Set the text alignment for the block.
	 *
	 * @since 1.0.0
	 *
	 * @param string $align The text alignment value (e.g., 'left', 'center', 'right').
	 * @return self Returns the instance for method chaining.
	 */
	public function textAlign( string $align ): self {
		if ( ! in_array( $align, $this->allowedTextAlignments, true ) ) {
			return $this;
		}

		// Set the align attribute in the block comment
		$this->blockAttributes['align'] = $align;

		// Add the text alignment class to the wrapper element
		$this->addClass( "has-text-align-{$align}" );

		return $this;
	}
}
