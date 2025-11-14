<?php
/**
 * Group Block Implementation
 *
 * Gutenberg group block markup generator with flex layout support.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Traits\AnchorTrait;
use MaxPertici\GutenbergMarkup\Traits\DropCapTrait;
use MaxPertici\GutenbergMarkup\Traits\TagNameTrait;
use MaxPertici\GutenbergMarkup\Traits\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Traits\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Traits\LinkColorTrait;
use MaxPertici\GutenbergMarkup\Traits\TextColorTrait;
use MaxPertici\GutenbergMarkup\Traits\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Traits\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Traits\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Traits\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Traits\TextTransformTrait;
use MaxPertici\GutenbergMarkup\Traits\TextDecorationTrait;
use MaxPertici\GutenbergMarkup\Traits\BackgroundColorTrait;

/**
 * Group Gutenberg Block implementation.
 *
 * Flex row layout block with support for:
 * - Flex layout (always flex, type is fixed)
 * - Orientation (horizontal or vertical)
 * - Justify content (left, center, right, space-between, stretch)
 * - Flex wrap (true/false)
 * - Custom HTML tag name (div, header, main, section, article, aside, footer)
 * - Custom CSS classes
 * - HTML anchors (IDs)
 * - Text and background colors (preset and custom)
 * - Inner blocks (children)
 *
 * @since 1.0.0
 */
class GroupRowBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use TagNameTrait;
	use TextColorTrait;
	use BackgroundColorTrait;
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

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $children         Array of child blocks (Markup objects or strings).
	 * @param bool   $wrap             Optional. Enable flex wrap. Default false.
	 * @param string $justify_content  Optional. Justify content alignment (left, center, right, space-between, stretch). Default empty.
	 * @param string $orientation      Optional. Layout orientation (horizontal or vertical). Default 'horizontal'.
	 * @param string $tag_name         Optional. HTML tag name for the wrapper (div, header, main, section, article, aside, footer). Default 'div'.
	 */
	public function __construct(
		array $children = [],
		bool $wrap = false,
		string $justify_content = '',
		string $orientation = 'horizontal',
		string $tag_name = 'div'
	) {
		// Build layout attributes
		$layout_attributes = array(
			'type' => 'flex',
		);

		// Add flexWrap if enabled
		if ( $wrap ) {
			$layout_attributes['flexWrap'] = 'wrap';
		}

		// Add justifyContent if specified
		if ( ! empty( $justify_content ) ) {
			$layout_attributes['justifyContent'] = $justify_content;
		}

		// Add orientation if vertical (horizontal is default)
		if ( 'vertical' === $orientation ) {
			$layout_attributes['orientation'] = 'vertical';
		}

		parent::__construct(
			block_name: 'core/group',
			block_attributes: array( 'layout' => $layout_attributes ),
			wrapper: '<div class="%classes%" %attributes%>%children%</div>',
			children: $children
		);

		// Set tag name using the trait (this will update wrapper and block attributes)
		if ( 'div' !== $tag_name ) {
			$this->tag_name( $tag_name );
		}

		// Process layout with named arguments
		$this->process_layout( $wrap, $justify_content, $orientation );
	}

	/**
	 * Process layout and add appropriate CSS classes.
	 *
	 * Handles flex layout configurations:
	 * - Always uses flex layout (type is fixed to 'flex')
	 * - Orientation (horizontal or vertical)
	 * - Justify content alignment
	 * - Flex wrap
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $wrap            Enable flex wrap.
	 * @param string $justify_content Justify content alignment.
	 * @param string $orientation     Layout orientation.
	 * @return void
	 */
	private function process_layout( bool $wrap, string $justify_content, string $orientation ): void {
		// Base class for group blocks
		$this->add_class( 'wp-block-group' );

		// Always use flex layout
		$this->add_class( 'is-layout-flex' );
		$this->add_class( 'wp-block-group-is-layout-flex' );

		// Process orientation
		if ( 'vertical' === $orientation ) {
			$this->add_class( 'is-vertical' );
		}

		// Process flex wrap
		if ( $wrap ) {
			$this->add_class( 'is-flex-wrap' );
		}

		// Process justify content
		if ( ! empty( $justify_content ) ) {
			$this->process_justify_content( $justify_content );
		}
	}

	/**
	 * Process justify content alignment.
	 *
	 * @since 1.0.0
	 *
	 * @param string $justify_content Justify content value.
	 * @return void
	 */
	private function process_justify_content( string $justify_content ): void {
		switch ( $justify_content ) {
			case 'left':
				$this->add_class( 'is-content-justification-left' );
				break;

			case 'center':
				$this->add_class( 'is-content-justification-center' );
				break;

			case 'right':
				$this->add_class( 'is-content-justification-right' );
				break;

			case 'space-between':
				$this->add_class( 'is-content-justification-space-between' );
				break;

			case 'stretch':
				$this->add_class( 'is-content-justification-stretch' );
				break;
		}
	}
}

