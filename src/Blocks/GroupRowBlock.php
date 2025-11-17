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
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\TagNameTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\LinkColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\DropCapTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextDecorationTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextTransformTrait;

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
	 * @param array  $children        Array of child blocks (Markup objects or strings).
	 * @param bool   $wrap            Optional. Enable flex wrap. Default false.
	 * @param string $justifyContent  Optional. Justify content alignment (left, center, right, space-between, stretch). Default empty.
	 * @param string $orientation     Optional. Layout orientation (horizontal or vertical). Default 'horizontal'.
	 * @param string $tagName         Optional. HTML tag name for the wrapper (div, header, main, section, article, aside, footer). Default 'div'.
	 */
	public function __construct(
		array $children = [],
		bool $wrap = false,
		string $justifyContent = '',
		string $orientation = 'horizontal',
		string $tagName = 'div'
	) {
		// Build layout attributes
		$layoutAttributes = array(
			'type' => 'flex',
		);

		// Add flexWrap if enabled
		if ( $wrap ) {
			$layoutAttributes['flexWrap'] = 'wrap';
		}

		// Add justifyContent if specified
		if ( ! empty( $justifyContent ) ) {
			$layoutAttributes['justifyContent'] = $justifyContent;
		}

		// Add orientation if vertical (horizontal is default)
		if ( 'vertical' === $orientation ) {
			$layoutAttributes['orientation'] = 'vertical';
		}

		parent::__construct(
			blockName: 'core/group',
			blockAttributes: array( 'layout' => $layoutAttributes ),
			wrapper: '<div class="%classes%" %attributes%>%children%</div>',
			children: $children
		);

		// Set tag name using the trait (this will update wrapper and block attributes)
		if ( 'div' !== $tagName ) {
			$this->tagName( $tagName );
		}

		// Process layout with named arguments
		$this->processLayout( $wrap, $justifyContent, $orientation );
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
	 * @param bool   $wrap           Enable flex wrap.
	 * @param string $justifyContent Justify content alignment.
	 * @param string $orientation    Layout orientation.
	 * @return void
	 */
	private function processLayout( bool $wrap, string $justifyContent, string $orientation ): void {
		// Base class for group blocks
		$this->addClass( 'wp-block-group' );

		// Always use flex layout
		$this->addClass( 'is-layout-flex' );
		$this->addClass( 'wp-block-group-is-layout-flex' );

		// Process orientation
		if ( 'vertical' === $orientation ) {
			$this->addClass( 'is-vertical' );
		}

		// Process flex wrap
		if ( $wrap ) {
			$this->addClass( 'is-flex-wrap' );
		}

		// Process justify content
		if ( ! empty( $justifyContent ) ) {
			$this->processJustifyContent( $justifyContent );
		}
	}

	/**
	 * Process justify content alignment.
	 *
	 * @since 1.0.0
	 *
	 * @param string $justifyContent Justify content value.
	 * @return void
	 */
	private function processJustifyContent( string $justifyContent ): void {
		switch ( $justifyContent ) {
			case 'left':
				$this->addClass( 'is-content-justification-left' );
				break;

			case 'center':
				$this->addClass( 'is-content-justification-center' );
				break;

			case 'right':
				$this->addClass( 'is-content-justification-right' );
				break;

			case 'space-between':
				$this->addClass( 'is-content-justification-space-between' );
				break;

			case 'stretch':
				$this->addClass( 'is-content-justification-stretch' );
				break;
		}
	}
}

