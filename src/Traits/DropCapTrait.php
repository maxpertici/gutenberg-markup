<?php
/**
 * Drop Cap Trait
 *
 * Provides drop cap functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Traits
 */

namespace MaxPertici\GutenbergMarkup\Traits;

/**
 * Drop Cap Trait.
 *
 * Adds drop cap support to Gutenberg blocks (typically paragraph blocks).
 * When enabled, adds the 'has-drop-cap' class to display the first letter
 * as a large decorative capital.
 *
 * @since 1.0.0
 */
trait DropCapTrait {

	/**
	 * Enable or disable drop cap for the block.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $enable Whether to enable drop cap. Default true.
	 * @return self Returns the instance for method chaining.
	 */
	public function drop_cap( bool $enable = true ): self {
		// Set the dropCap attribute
		$this->block_attributes['dropCap'] = $enable;

		// Add or remove the has-drop-cap class
		if ( $enable ) {
			$this->add_class( 'has-drop-cap' );
		} else {
			$this->remove_class( 'has-drop-cap' );
		}

		return $this;
	}
}

