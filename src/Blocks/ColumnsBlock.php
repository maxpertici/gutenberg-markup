<?php
/**
 * Columns Block Implementation
 *
 * Gutenberg columns block markup generator.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Block\BlockStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Dimensions\MarginTrait;
use MaxPertici\GutenbergMarkup\Concerns\Dimensions\PaddingTrait;
use MaxPertici\GutenbergMarkup\Concerns\Layout\AlignTrait;

/**
 * Columns Gutenberg Block implementation.
 *
 * Container block for multiple column blocks with optional alignment
 * and mobile-stacking behaviour.
 *
 * @since 1.0.0
 */
class ColumnsBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use TextColorTrait;
	use AlignTrait;
	use BlockStyleTrait;
	use MarginTrait;
	use PaddingTrait;

	/**
	 * Whether columns should stack on mobile.
	 *
	 * Defaults to true (stacked). When false, the `is-not-stacked-on-mobile`
	 * class and `{"isStackedOnMobile":false}` attribute are added.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $isStackedOnMobile = true;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $children Optional. Array of ColumnBlock children. Default empty.
	 */
	public function __construct( array $children = [] ) {
		// Accept only ColumnBlock instances.
		$children = array_values(
			array_filter(
				$children,
				fn( $child ) => $child instanceof ColumnBlock
			)
		);

		parent::__construct(
			blockName: 'core/columns',
			blockAttributes: array(),
			wrapper: '<div class="%classes%" %attributes%>%children%</div>',
			children: $children
		);
	}

	/**
	 * Gets or sets whether columns stack on mobile.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|null $stacked True to enable stacking (default), false to disable.
	 * @return bool|self Returns the current value when no argument is given, otherwise $this.
	 */
	public function isStackedOnMobile( ?bool $stacked = null ) {
		if ( null === $stacked ) {
			return $this->isStackedOnMobile;
		}

		$this->isStackedOnMobile = $stacked;
		return $this;
	}

	/**
	 * Disable mobile stacking (shorthand helper).
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns $this for method chaining.
	 */
	public function notStackedOnMobile(): self {
		$this->isStackedOnMobile = false;
		return $this;
	}

	/**
	 * Build classes and block attributes before rendering.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function build(): void {
		$this->addClass( 'wp-block-columns' );

		if ( ! $this->isStackedOnMobile ) {
			$this->setBlockAttributes( array( 'isStackedOnMobile' => false ) );
			$this->addClass( 'is-not-stacked-on-mobile' );
		}

		if ( null !== $this->align && 'none' !== $this->align ) {
			$this->addClass( 'align' . $this->align );
		}
	}

	/**
	 * Gets the complete block markup with Gutenberg comments.
	 *
	 * @since 1.0.0
	 *
	 * @return string The complete block markup including Gutenberg comment syntax.
	 */
	public function render(): string {
		$this->build();
		return parent::render();
	}

	/**
	 * Prints the complete block markup with Gutenberg comments.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print(): void {
		$this->build();
		parent::print();
	}

	/**
	 * Hydrate runtime state from parsed attrs.
	 *
	 * @param array $attributes Parsed Gutenberg attrs.
	 * @param bool  $merge Merge or replace attributes.
	 * @return self
	 */
	public function hydrate( array $attributes, bool $merge = false ): self {
		parent::hydrate( $attributes, $merge );

		if ( array_key_exists( 'isStackedOnMobile', $attributes ) ) {
			$this->isStackedOnMobile( (bool) $attributes['isStackedOnMobile'] );
		}

		return $this;
	}
}
