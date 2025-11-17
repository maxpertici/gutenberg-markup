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
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\LinkColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Flex\FlexWidthTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\DropCapTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextDecorationTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextTransformTrait;

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
			blockName: 'core/paragraph',
			blockAttributes: $attributes,
			wrapper: '<p class="%classes%" %attributes%>%children%</p>',
			children: [ $content ]
		);
	}
}

