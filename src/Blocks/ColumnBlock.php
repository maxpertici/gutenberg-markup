<?php
/**
 * Column Block Implementation
 *
 * Gutenberg column block markup generator.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Block\BlockStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Block\InnerBlocksSupportTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Dimensions\MarginTrait;
use MaxPertici\GutenbergMarkup\Concerns\Dimensions\PaddingTrait;

/**
 * Column Gutenberg Block implementation.
 *
 * Represents a single column inside a ColumnsBlock. Supports optional
 * width (as a percentage or any CSS value) and an optional constrained
 * inner layout.
 *
 * @since 1.0.0
 */
class ColumnBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use InnerBlocksSupportTrait;
	use TextColorTrait;
	use BlockStyleTrait;
	use MarginTrait;
	use PaddingTrait;

	/**
	 * Block attribute: width.
	 *
	 * When set, adds `{"width":"<value>"}` to the block attributes and
	 * `style="flex-basis:<value>"` to the wrapper element.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $width = null;

	/**
	 * Block attribute: layout.
	 *
	 * An optional layout descriptor, e.g. `['type' => 'constrained']`.
	 *
	 * @since 1.0.0
	 * @var array|null
	 */
	protected ?array $layout = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $children Optional. Array of child blocks or strings. Default empty.
	 */
	public function __construct( array $children = [] ) {
		parent::__construct(
			blockName: 'core/column',
			blockAttributes: array(),
			wrapper: '<div class="%classes%" %attributes%>%children%</div>',
			children: $children
		);
	}

	/**
	 * Gets or sets the column width.
	 *
	 * When set, a `style="flex-basis:<value>"` attribute and a
	 * `{"width":"<value>"}` block attribute are applied.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $width Optional. Width value (e.g. '30%', '200px'). Pass null to get.
	 * @return string|self|null Returns the width when getting, or $this when setting.
	 */
	public function width( ?string $width = null ) {
		if ( null === $width ) {
			return $this->width;
		}

		$this->width = $width;
		return $this;
	}

	/**
	 * Gets or sets the column inner layout.
	 *
	 * @since 1.0.0
	 *
	 * @param array|null $layout Optional. Layout descriptor array. Pass null to get.
	 * @return array|self|null Returns the layout when getting, or $this when setting.
	 */
	public function layout( ?array $layout = null ) {
		if ( null === $layout ) {
			return $this->layout;
		}

		$this->layout = $layout;
		return $this;
	}

	/**
	 * Set the inner layout to constrained.
	 *
	 * Shorthand for `->layout(['type' => 'constrained'])`.
	 *
	 * @since 1.0.0
	 *
	 * @return self Returns $this for method chaining.
	 */
	public function layoutConstrained(): self {
		$this->layout = array( 'type' => 'constrained' );
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
		$this->addClass( 'wp-block-column' );

		if ( null !== $this->width ) {
			$this->setBlockAttributes( array( 'width' => $this->width ) );
			$safeWidth = \function_exists( 'esc_attr' ) ? \esc_attr( $this->width ) : htmlspecialchars( $this->width, ENT_QUOTES );
			$this->setAttribute( 'style', 'flex-basis:' . $safeWidth );
		}

		if ( null !== $this->layout ) {
			$this->setBlockAttributes( array( 'layout' => $this->layout ) );
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

		if ( isset( $attributes['width'] ) ) {
			$this->width( (string) $attributes['width'] );
		}

		if ( isset( $attributes['layout'] ) && is_array( $attributes['layout'] ) ) {
			$this->layout( $attributes['layout'] );
		}

		return $this;
	}
}
