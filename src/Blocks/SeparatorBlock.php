<?php
/**
 * Separator Block Implementation
 *
 * Gutenberg separator block markup generator.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Layout\AlignTrait;

/**
 * Separator Gutenberg Block implementation.
 *
 * Comprehensive separator block with support for:
 * - Custom CSS classes
 * - Background color (preset and custom)
 * - Alignment
 * - Opacity control
 *
 * @since 1.0.0
 */
class SeparatorBlock extends BlockMarkup {

	use AlignTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;

	/**
	 * Whether to include the alpha channel opacity class.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $hasAlphaChannelOpacity;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes              Optional. Block attributes. Default empty array.
	 * @param bool  $hasAlphaChannelOpacity  Optional. Whether to include alpha channel opacity. Default true.
	 */
	public function __construct( array $attributes = [], bool $hasAlphaChannelOpacity = true ) {
		$this->hasAlphaChannelOpacity = $hasAlphaChannelOpacity;

		parent::__construct(
			blockName: 'core/separator',
			blockAttributes: $attributes,
			wrapper: '<hr class="wp-block-separator %classes%" %attributes%/>',
			children: []
		);
	}

	/**
	 * Sets or gets the alpha channel opacity setting.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|null $value Optional. The alpha channel opacity value. Default null.
	 * @return bool|self Returns the current value when getting, or the instance for method chaining when setting.
	 */
	public function hasAlphaChannelOpacity( ?bool $value = null ) {
		// Getter: return current value
		if ( null === $value ) {
			return $this->hasAlphaChannelOpacity;
		}

		// Setter: set value
		$this->hasAlphaChannelOpacity = $value;

		return $this;
	}

	/**
	 * Builds the wrapper and block attributes before rendering.
	 *
	 * Adds the alpha channel opacity class if enabled and prepares
	 * the wrapper HTML with appropriate classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function buildWrapper(): void {
		// Add alpha channel opacity class if enabled
		if ( $this->hasAlphaChannelOpacity ) {
			$this->wrapperClass[] = 'has-alpha-channel-opacity';
		}

		// Rebuild wrapper with classes
		$this->wrapper = '<hr class="wp-block-separator %classes%" %attributes%/>';
	}

	/**
	 * Gets the complete block markup with Gutenberg comments.
	 *
	 * Builds the wrapper and attributes before rendering the block.
	 *
	 * @since 1.0.0
	 *
	 * @return string The complete block markup including Gutenberg comment syntax.
	 */
	public function render(): string {
		$this->buildWrapper();
		return parent::render();
	}

	/**
	 * Prints the complete block markup with Gutenberg comments (echo mode).
	 *
	 * Builds the wrapper and attributes before printing the block.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print(): void {
		$this->buildWrapper();
		parent::print();
	}
}

