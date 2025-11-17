<?php
/**
 * Align Trait
 *
 * Provides alignment functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Advanced
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Layout;

/**
 * Align Trait.
 *
 * Adds alignment support to Gutenberg blocks.
 * This allows blocks to be aligned left, center, right, wide, or full width.
 *
 * @since 1.0.0
 */
trait AlignTrait {

	/**
	 * The alignment value for the block.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $align = null;

	/**
	 * List of allowed alignment values.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $allowedAlignments = array(
		'left',
		'center',
		'right',
		'wide',
		'full',
		'none',
	);

	/**
	 * Get or set the alignment for the block.
	 *
	 * When called without parameters, returns the current alignment value.
	 * When called with a parameter, sets the alignment value.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $align Optional. The alignment value (left, center, right, wide, full, none).
	 *                           If null, returns the current alignment. Default null.
	 * @return self|string|null Returns the instance for method chaining when setting,
	 *                          or the current alignment value when getting.
	 */
	public function align( ?string $align = null ) {
		// Getter: return current value if no parameter provided
		if ( null === $align ) {
			return $this->align;
		}

		// Setter: validate and set alignment
		$align = strtolower( trim( $align ) );

		if ( ! in_array( $align, $this->allowedAlignments, true ) ) {
			return $this; // Return without setting if invalid
		}

		$this->align = $align;

		// Add align to block attributes if not 'none'
		if ( 'none' !== $align ) {
			$this->blockAttributes['align'] = $align;
		} else {
			// Remove align from attributes if set to 'none'
			unset( $this->blockAttributes['align'] );
		}

		return $this;
	}

	/**
	 * Set alignment to left.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function alignLeft(): self {
		return $this->align( 'left' );
	}

	/**
	 * Set alignment to center.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function alignCenter(): self {
		return $this->align( 'center' );
	}

	/**
	 * Set alignment to right.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function alignRight(): self {
		return $this->align( 'right' );
	}

	/**
	 * Set alignment to wide.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function alignWide(): self {
		return $this->align( 'wide' );
	}

	/**
	 * Set alignment to full.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function alignFull(): self {
		return $this->align( 'full' );
	}

	/**
	 * Remove alignment (set to none).
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function alignNone(): self {
		return $this->align( 'none' );
	}
}


