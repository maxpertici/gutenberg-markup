<?php
/**
 * Pullquote Block Implementation
 *
 * Gutenberg pullquote block markup generator.
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
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontSizeTrait;

/**
 * Pullquote Gutenberg Block implementation.
 *
 * Generates a `<figure class="wp-block-pullquote">` element that wraps a
 * `<blockquote>` containing a `<p>` with the quoted text and an optional
 * `<cite>` citation.
 *
 * Supports `textAlign` which maps to the Gutenberg `{"textAlign":"..."}`
 * block attribute and a `has-text-align-{value}` CSS class.
 *
 * @since 1.0.0
 */
class PullquoteBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use TextColorTrait;
	use FontSizeTrait;
	use BlockStyleTrait;
	use InnerBlocksSupportTrait;
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
	 * The quoted text (rendered inside a `<p>` within the blockquote).
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $value = '';

	/**
	 * Optional citation displayed inside a `<cite>` element.
	 *
	 * @since 1.0.0
	 * @var string|null
	 */
	protected ?string $citation = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $valueOrChildren The quoted text or parsed inner children.
	 * @param string|null $citation Optional. Citation text for the `<cite>` element. Default null.
	 */
	public function __construct( array|string $valueOrChildren = '', ?string $citation = null ) {
		if ( is_array( $valueOrChildren ) ) {
			$this->setInnerBlocks( $valueOrChildren );
			$this->value = '';
		} else {
			$this->value = $valueOrChildren;
		}

		$this->citation = $citation;

		parent::__construct(
			blockName: 'core/pullquote',
			blockAttributes: array(),
			wrapper: '<figure class="%classes%" %attributes%>%children%</figure>',
			children: array()
		);
	}

	/**
	 * Gets or sets the quoted text.
	 *
	 * @since 1.0.0
	 *
	 * @param string|null $value Optional. Quoted text. Pass null to get.
	 * @return string|self Returns the current value when getting, or $this when setting.
	 */
	public function value( ?string $value = null ) {
		if ( null === $value ) {
			return $this->value;
		}

		$this->value = $value;
		return $this;
	}

	/**
	 * Gets or sets the text alignment.
	 *
	 * For the pullquote block, this maps to the `textAlign` block attribute
	 * and adds a `has-text-align-{value}` CSS class.
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
	 * Build the inner markup and block attributes before rendering.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function build(): void {
		$this->addClass( 'wp-block-pullquote' );

		if ( $this->hasInnerBlocks() ) {
			$inner = $this->renderInnerBlocks();
		} else {
			// Allow inline HTML in quoted text (same as wp_kses_post context) but escape
			// plain citation which should not contain markup.
			$safeValue = \function_exists( 'wp_kses_post' ) ? \wp_kses_post( $this->value ) : $this->value;
			$inner     = '<p>' . $safeValue . '</p>';
		}

		if ( null !== $this->citation && '' !== $this->citation ) {
			$safeCitation = \function_exists( 'esc_html' ) ? \esc_html( $this->citation ) : htmlspecialchars( $this->citation, ENT_QUOTES );
			$inner       .= '<cite>' . $safeCitation . '</cite>';
		}

		$this->children = array( '<blockquote>' . $inner . '</blockquote>' );
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

		if ( isset( $attributes['textAlign'] ) ) {
			$this->textAlign( (string) $attributes['textAlign'] );
		}

		return $this;
	}
}
