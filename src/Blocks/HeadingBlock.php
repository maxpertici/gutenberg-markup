<?php
/**
 * Heading Block Implementation
 *
 * Gutenberg heading block markup generator.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Flex\FlexWidthTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextDecorationTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextTransformTrait;

/**
 * Heading Gutenberg Block implementation.
 *
 * Comprehensive heading block with support for:
 * - Heading levels (h1-h6)
 * - Custom CSS classes
 * - HTML anchors (IDs)
 * - Text and background colors (preset and custom)
 * - Font sizes (preset and custom)
 * - Typography styles (weight, style, spacing, decoration, transform, line height)
 *
 * @since 1.0.0
 */
class HeadingBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use FontSizeTrait;
	use FontStyleTrait;
	use FontWeightTrait;
	use LetterSpacingTrait;
	use LineHeightTrait;
	use TextColorTrait;
	use TextDecorationTrait;
	use TextTransformTrait;
	use FlexWidthTrait;

	/**
	 * The heading level (1-6).
	 *
	 * Determines which HTML heading tag to use (h1, h2, h3, h4, h5, or h6).
	 * Default is 2 (h2) to match Gutenberg's default behavior.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $level;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    The heading content.
	 * @param int    $level      Optional. The heading level (1-6). Default 2.
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 */
	public function __construct( string $content, int $level = 2, array $attributes = [] ) {
		// Set the heading level
		$this->level( $level );

		// Initialize parent BlockMarkup
		parent::__construct(
			blockName: 'core/heading',
			blockAttributes: $attributes,
			children: [ $content ]
		);
	}

	/**
	 * Gets or sets the heading level.
	 *
	 * When called without arguments, returns the current heading level.
	 * When called with a level argument, validates and sets the heading level,
	 * ensuring it's between 1 and 6. If an invalid level is provided, defaults to 2.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $level Optional. The heading level (1-6) to set. Default null.
	 * @return int|self Returns the level when getting, or the instance for method chaining when setting.
	 */
	public function level( ?int $level = null ) {
		// Getter: return current level
		if ( null === $level ) {
			return $this->level;
		}

		// Setter: validate and set level
		// Validate level is between 1 and 6
		if ( $level < 1 || $level > 6 ) {
			$level = 2; // Default to h2
		}

		$this->level = $level;

		return $this;
	}

	/**
	 * Builds the wrapper and block attributes before rendering.
	 *
	 * Generates the wrapper HTML with the correct heading level and updates
	 * the block attributes with the level if it's not the default (2).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function buildWrapper(): void {
		// Update block attributes with level if not default (2)
		if ( 2 !== $this->level ) {
			$this->blockAttributes['level'] = $this->level;
		} else {
			// Remove level attribute if it's the default (2)
			unset( $this->blockAttributes['level'] );
		}

		// Build wrapper with current level
		$this->wrapper = sprintf(
			'<h%d class="wp-block-heading %%classes%%" %%attributes%%>%%children%%</h%d>',
			$this->level,
			$this->level
		);
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

