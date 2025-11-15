<?php
/**
 * Paragraph Block Implementation
 *
 * Gutenberg paragraph block markup generator.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\DropCapTrait;
use MaxPertici\GutenbergMarkup\Concerns\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Concerns\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Concerns\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\LinkColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\TextDecorationTrait;
use MaxPertici\GutenbergMarkup\Concerns\TextTransformTrait;
use MaxPertici\GutenbergMarkup\Concerns\FlexWidthTrait;

/**
 * Paragraph Gutenberg Block implementation.
 *
 * Comprehensive paragraph block with support for:
 * - Drop cap styling
 * - Custom CSS classes
 * - HTML anchors (IDs)
 * - Text and background colors (preset and custom)
 * - Font sizes (preset and custom)
 * - Typography styles (weight, style, spacing, decoration, transform, line height)
 * - Link colors
 *
 * @since 1.0.0
 */
class ParagraphBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use DropCapTrait;
	use FontSizeTrait;
	use FontStyleTrait;
	use FontWeightTrait;
	use LetterSpacingTrait;
	use LineHeightTrait;
	use LinkColorTrait;
	use TextColorTrait;
	use TextDecorationTrait;
	use TextTransformTrait;
	use FlexWidthTrait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content    The paragraph content.
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 */
	public function __construct( string $content, array $attributes = [] ) {
		parent::__construct(
			block_name: 'core/paragraph',
			block_attributes: $attributes,
			wrapper: '<p class="%classes%" %attributes%>%children%</p>',
			children: [ $content ]
		);
	}
}

