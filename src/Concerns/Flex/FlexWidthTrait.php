<?php
/**
 * Flex Width Trait
 *
 * Provides width and flex functionality for Gutenberg blocks using native selfStretch and flexSize.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Flex
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Flex;

/**
 * Flex Width Trait.
 *
 * Adds width and flex support to Gutenberg blocks using native Gutenberg layout properties.
 * Uses selfStretch (fill, fixed, fit) and flexSize for proper Gutenberg integration.
 * Particularly useful for child elements in flex layouts.
 *
 * @since 1.0.0
 */
trait FlexWidthTrait {

	/**
	 * Set selfStretch to 'fit' - element fits to content.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function fit(): self {
		$this->blockAttributes['style']['layout']['selfStretch'] = 'fit';
		$this->blockAttributes['style']['layout']['flexSize']    = null;

		return $this;
	}

	/**
	 * Set selfStretch to 'fill' - element fills available space (grows).
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns the instance for method chaining.
	 */
	public function grow(): self {
		$this->blockAttributes['style']['layout']['selfStretch'] = 'fill';
		$this->blockAttributes['style']['layout']['flexSize']    = null;

		return $this;
	}

	/**
	 * Set selfStretch to 'fixed' with a specific size.
	 *
	 * @since 1.0.0
	 *
	 * @param string $size The fixed size (e.g., '100px', '50%', '10rem', '100vw', '100vh').
	 * @return self Returns the instance for method chaining.
	 */
	public function fixed( string $size ): self {
		$this->blockAttributes['style']['layout']['selfStretch'] = 'fixed';
		$this->blockAttributes['style']['layout']['flexSize']    = $size;

		return $this;
	}

	/**
	 * Alias: Set fixed width using px.
	 *
	 * @since 1.0.0
	 *
	 * @param string $size The size value (e.g., '100px', '50%', '10rem').
	 * @return self Returns the instance for method chaining.
	 */
	public function width( string $size ): self {
		return $this->fixed( $size );
	}
}

