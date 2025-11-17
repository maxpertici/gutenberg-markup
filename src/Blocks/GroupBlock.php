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
use MaxPertici\GutenbergMarkup\Concerns\Layout\AlignTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\LinkColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Layout\PositionTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\TagNameTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\DropCapTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextTransformTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextDecorationTrait;

/**
 * Group Gutenberg Block implementation.
 *
 * @since 1.0.0
 */
class GroupBlock extends BlockMarkup {

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
	use AlignTrait;
	use PositionTrait;
	
	/**
	 * Layout type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $layoutType = 'default';

	/**
	 * Layout orientation (for flex layout).
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $layoutOrientation = null;

	/**
	 * Flex wrap setting.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $layoutFlexWrap = null;

	/**
	 * Justify content setting.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $layoutJustifyContent = null;

	/**
	 * Content size for constrained layout.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $layoutContentSize = null;

	/**
	 * Wide size for constrained layout.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	private ?string $layoutWideSize = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $children Array of child blocks (Markup objects or strings).
	 */
	public function __construct( array $children = [] ) {
		parent::__construct(
			blockName: 'core/group',
			blockAttributes: array(),
			wrapper: '<div class="%classes%" %attributes%>%children%</div>',
			children: $children
		);
	}

	/**
	 * Build layout attributes from properties.
	 *
	 * This method constructs the final layout attributes based on
	 * the current state of layout properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function buildLayoutAttributes(): void {
		$layoutAttributes = array(
			'type' => $this->layoutType,
		);

		// Add orientation for flex vertical
		if ( null !== $this->layoutOrientation ) {
			$layoutAttributes['orientation'] = $this->layoutOrientation;
		}

		// Add flexWrap if set
		if ( null !== $this->layoutFlexWrap ) {
			$layoutAttributes['flexWrap'] = $this->layoutFlexWrap;
		}

		// Add justifyContent if set
		if ( null !== $this->layoutJustifyContent ) {
			$layoutAttributes['justifyContent'] = $this->layoutJustifyContent;
		}

		// Add contentSize if set (constrained layout)
		if ( null !== $this->layoutContentSize ) {
			$layoutAttributes['contentSize'] = $this->layoutContentSize;
		}

		// Add wideSize if set (constrained layout)
		if ( null !== $this->layoutWideSize ) {
			$layoutAttributes['wideSize'] = $this->layoutWideSize;
		}

		$this->setBlockAttributes( array( 'layout' => $layoutAttributes ), true );
	}

	/**
	 * Generate CSS classes and build layout attributes before rendering.
	 *
	 * This method is called before rendering to compute the appropriate
	 * CSS classes and construct final layout attributes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function applyLayoutClasses(): void {
		// Build layout attributes from properties
		$this->buildLayoutAttributes();

		// Only add base class - WordPress handles layout via JSON attributes
		$this->addClass( 'wp-block-group' );
	}

	/**
	 * Switch to flex row layout.
	 *
	 * Converts the group block to use flex layout in row orientation.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|null $wrap Optional. Enable flex wrap (true for 'wrap', false for 'nowrap', null to not set). Default null.
	 * @return self Returns $this for method chaining.
	 */
	public function asFlexRow( ?bool $wrap = null ): self {
		$this->layoutType = 'flex';
		$this->layoutOrientation = null; // Reset orientation (row is default)

		// Only set flexWrap if explicitly provided
		if ( null !== $wrap ) {
			$this->layoutFlexWrap = $wrap ? 'wrap' : 'nowrap';
		}

		return $this;
	}

	/**
	 * Switch to flex column layout.
	 *
	 * Converts the group block to use flex layout in column orientation (vertical).
	 *
	 * @since 1.0.0
	 *
	 * @param bool|null $wrap Optional. Enable flex wrap (true for 'wrap', false for 'nowrap', null to not set). Default null.
	 * @return self Returns $this for method chaining.
	 */
	public function asFlexColumn( ?bool $wrap = null ): self {
		$this->layoutType = 'flex';
		$this->layoutOrientation = 'vertical';

		// Only set flexWrap if explicitly provided
		if ( null !== $wrap ) {
			$this->layoutFlexWrap = $wrap ? 'wrap' : 'nowrap';
		}

		return $this;
	}

	/**
	 * Switch to block layout (flow).
	 *
	 * Converts the group block to use flow layout (basic block layout).
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns $this for method chaining.
	 */
	public function asBlock(): self {
		$this->layoutType = 'flow';
		$this->layoutOrientation = null;
		$this->layoutFlexWrap = null;

		return $this;
	}

	/**
	 * Enable or disable constrained layout.
	 *
	 * When enabled, converts the group block to use constrained layout (standard WordPress group block with max-width).
	 * When disabled, reverts to default layout (only works if current layout is not flex).
	 *
	 * @since 1.0.0
	 *
	 * @param bool $enable True to enable constrained layout, false to revert to default layout. Default true.
	 * @return self Returns $this for method chaining.
	 */
	public function layoutConstrained( bool $enable = true ): self {
		if ( $enable ) {
			// Enable constrained layout
			$this->layoutType = 'constrained';
			$this->layoutOrientation = null;
		} else {
			// Revert to default layout only if not in flex mode
			if ( 'flex' !== $this->layoutType ) {
				$this->layoutType = 'default';
				$this->layoutOrientation = null;
			}
			// If flex, keep current layout unchanged
		}

		return $this;
	}

	/**
	 * Set flex wrap.
	 *
	 * Controls whether flex items should wrap or not (only applies to flex layout).
	 *
	 * @since 1.0.0
	 *
	 * @param bool $enable True for 'wrap', false for 'nowrap'.
	 * @return self Returns $this for method chaining.
	 */
	public function wrap( bool $enable = true ): self {
		if ( 'flex' === $this->layoutType ) {
			$this->layoutFlexWrap = $enable ? 'wrap' : 'nowrap';
		}

		return $this;
	}

	/**
	 * Set justify content alignment for flex, constrained or default layout.
	 *
	 * @since 1.0.0
	 *
	 * @param string $justifyContent Justify content value (left, center, right, space-between, stretch).
	 * @return self Returns $this for method chaining.
	 */
	public function justifyContent( string $justifyContent ): self {
		if ( in_array( $this->layoutType, array( 'flex', 'constrained', 'default' ), true ) ) {
			$this->layoutJustifyContent = $justifyContent;
		}

		return $this;
	}

	/**
	 * Set content size for constrained layout.
	 *
	 * Defines the default max-width for content in constrained layout.
	 * This applies to blocks that don't use align wide or align full.
	 *
	 * @since 1.0.0
	 *
	 * @param string $contentSize Content size value (e.g., '600px', '40rem', '100%').
	 * @return self Returns $this for method chaining.
	 */
	public function contentSize( string $contentSize ): self {
		if ( 'constrained' === $this->layoutType ) {
			$this->layoutContentSize = $contentSize;
		}

		return $this;
	}

	/**
	 * Set wide size for constrained layout.
	 *
	 * Defines the max-width for blocks with align wide in constrained layout.
	 *
	 * @since 1.0.0
	 *
	 * @param string $wideSize Wide size value (e.g., '1200px', '80rem', '100%').
	 * @return self Returns $this for method chaining.
	 */
	public function wideSize( string $wideSize ): self {
		if ( 'constrained' === $this->layoutType ) {
			$this->layoutWideSize = $wideSize;
		}

		return $this;
	}

	/**
	 * Override render to apply layout classes before rendering.
	 *
	 * @since 1.0.0
	 *
	 * @return string The rendered block markup.
	 */
	public function render(): string {
		$this->applyLayoutClasses();
		return parent::render();
	}

	/**
	 * Override echo method to apply layout classes before echoing.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function echo(): void {
		$this->applyLayoutClasses();
		parent::echo();
	}
}

