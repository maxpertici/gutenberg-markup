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
use MaxPertici\GutenbergMarkup\Traits\TextColorTrait;
use MaxPertici\GutenbergMarkup\Traits\BackgroundColorTrait;

/**
 * Paragraph Gutenberg Block implementation.
 *
 * Simple class to create paragraph blocks with Gutenberg markup.
 *
 * @since 1.0.0
 */
class ParagraphBlock extends BlockMarkup {

	use BackgroundColorTrait;
	use TextColorTrait;

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

