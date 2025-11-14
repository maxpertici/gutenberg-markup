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
use MaxPertici\GutenbergMarkup\Traits\AnchorTrait;
use MaxPertici\GutenbergMarkup\Traits\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Traits\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Traits\DropCapTrait;
use MaxPertici\GutenbergMarkup\Traits\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Traits\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Traits\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Traits\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Traits\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Traits\LinkColorTrait;
use MaxPertici\GutenbergMarkup\Traits\TextColorTrait;
use MaxPertici\GutenbergMarkup\Traits\TextDecorationTrait;
use MaxPertici\GutenbergMarkup\Traits\TextTransformTrait;
use MaxPertici\GutenbergMarkup\Traits\FlexWidthTrait;

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

