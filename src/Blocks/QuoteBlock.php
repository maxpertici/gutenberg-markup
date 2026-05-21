<?php
/**
 * Quote Block Implementation
 *
 * Gutenberg quote block markup generator.
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
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontSizeTrait;

/**
 * Quote Gutenberg Block implementation.
 *
 * Generates a `<blockquote>` element that wraps inner block children and
 * an optional `<cite>` citation. Supports `textAlign` which maps to the
 * Gutenberg `{"textAlign":"..."}` block attribute and a
 * `has-text-align-{value}` CSS class.
 *
 * @since 1.0.0
 */
class QuoteBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use TextColorTrait;
	use FontSizeTrait;
	use BlockStyleTrait;
	use MarginTrait;
	use PaddingTrait;

	/**
	 * Block attribute: textAlign.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $textAlign = null;

	/**
	 * Allowed text-alignment values.
	 *
	 * @since 1.0.0
	 * @var array<string>
	 */
	protected array $allowedTextAlignments = array( 'left', 'center', 'right' );

	/**
	 * Optional citation displayed inside a `<cite>` element.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $citation = null;

	/**
	 * Inner block children stored before build.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $innerBlocks = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array       $children  Optional. Array of child blocks (e.g. ParagraphBlock). Default empty.
	 * @param string|null $citation  Optional. Citation text for the `<cite>` element. Default null.
	 */
	public function __construct( array $children = [], ?string $citation = null ) {
		$this->innerBlocks = $children;
		$this->citation    = $citation;

		parent::__construct(
			blockName: 'core/quote',
			blockAttributes: array(),
			wrapper: '<blockquote class="%classes%" %attributes%>%children%</blockquote>',
			children: array()
		);
	}

	/**
	 * Gets or sets the text alignment.
	 *
	 * For the quote block, this maps to the `textAlign` block attribute
	 * (not `align`) and adds a `has-text-align-{value}` CSS class.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $align Optional. Alignment value (left, center, right). Pass null to get.
	 * @return string|self|null Returns the current value when getting, or $this when setting.
	 */
	public function textAlign( ?string $align = null ) {
		if ( null === $align ) {
			return $this->textAlign;
		}

		if ( ! in_array( $align, $this->allowedTextAlignments, true ) ) {
			return $this;
		}

		$this->textAlign                    = $align;
		$this->blockAttributes['textAlign'] = $align;
		$this->addClass( "has-text-align-{$align}" );

		return $this;
	}

	/**
	 * Align text to the left.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public function textAlignLeft(): self {
		return $this->textAlign( 'left' );
	}

	/**
	 * Center the text alignment.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public function textAlignCenter(): self {
		return $this->textAlign( 'center' );
	}

	/**
	 * Align text to the right.
	 *
	 * @since 1.0.0
	 *
	 * @return self
	 */
	public function textAlignRight(): self {
		return $this->textAlign( 'right' );
	}

	/**
	 * Gets or sets the citation text.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $citation Optional. Citation text. Pass null to get.
	 * @return string|self|null Returns the current citation when getting, or $this when setting.
	 */
	public function citation( ?string $citation = null ) {
		if ( null === $citation ) {
			return $this->citation;
		}

		$this->citation = $citation;
		return $this;
	}

	/**
	 * Build children and block attributes before rendering.
	 *
	 * Appends the `<cite>` element (if set) after the inner block children.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function build(): void {
		$this->addClass( 'wp-block-quote' );

		$children = $this->innerBlocks;

		if ( null !== $this->citation && '' !== $this->citation ) {
			$safeCitation = \function_exists( 'esc_html' ) ? \esc_html( $this->citation ) : htmlspecialchars( $this->citation, ENT_QUOTES );
			$children[]   = '<cite>' . $safeCitation . '</cite>';
		}

		$this->children = $children;
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
}
