<?php
/**
 * Anchor Trait
 *
 * Provides HTML anchor (ID) functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns
 */

namespace MaxPertici\GutenbergMarkup\Concerns;

/**
 * Anchor Trait.
 *
 * Adds HTML anchor/ID support to Gutenberg blocks.
 * This allows blocks to be linked to directly via URL fragments.
 *
 * @since 1.0.0
 */
trait AnchorTrait {

	/**
	 * Set the HTML anchor (ID) for the block.
	 *
	 * @since 1.0.0
	 *
	 * @param string $anchor The anchor/ID value (without the # symbol).
	 * @return self Returns the instance for method chaining.
	 */
	public function anchor( string $anchor ): self {
		// Set the anchor attribute
		$this->block_attributes['anchor'] = $anchor;

		// Add the ID to the wrapper attributes
		$this->wrapper_attributes['id'] = $anchor;

		return $this;
	}
}

