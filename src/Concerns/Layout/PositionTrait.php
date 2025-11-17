<?php
/**
 * Position Trait
 *
 * Provides position functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Layout
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Layout;

/**
 * Position Trait.
 *
 * Adds position support to Gutenberg blocks.
 * This allows blocks to have sticky positioning or reset to default (no positioning).
 *
 * @since 1.0.0
 */
trait PositionTrait {

	/**
	 * The position value for the block.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $position = null;

	/**
	 * The top offset value for the positioned block.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $top = '0px';

	/**
	 * List of allowed position values.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $allowedPositions = array(
		'default',
		'sticky',
	);

	/**
	 * Get or set the position for the block.
	 *
	 * When called without parameters, returns the current position value.
	 * When called with a parameter, sets the position value.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $position Optional. The position value (default, sticky).
	 *                              If null, returns the current position. Default null.
	 * @return self|string|null Returns the instance for method chaining when setting,
	 *                          or the current position value when getting.
	 */
	public function position( ?string $position = null ) {
		// Getter: return current value if no parameter provided
		if ( null === $position ) {
			return $this->position;
		}

		// Setter: validate and set position
		$position = strtolower( trim( $position ) );

		if ( ! in_array( $position, $this->allowedPositions, true ) ) {
			return $this; // Return without setting if invalid
		}

		$this->position = $position;

		// If position is default, remove position attributes
		if ( 'default' === $position ) {
			unset( $this->blockAttributes['style']['position'] );
		} else {
			// Add position to block attributes style
			$this->blockAttributes['style']['position']['type'] = $position;
			$this->blockAttributes['style']['position']['top']  = $this->top;
		}

		return $this;
	}

	/**
	 * Set position to default (removes position attributes).
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function positionDefault(): self {
		return $this->position( 'default' );
	}

	/**
	 * Set position to sticky.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function positionSticky(): self {
		return $this->position( 'sticky' );
	}
}


