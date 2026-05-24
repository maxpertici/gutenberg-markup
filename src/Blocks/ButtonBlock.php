<?php
/**
 * Button Block Implementation
 *
 * Minimal Gutenberg button block markup generator.
 *
 * @package MaxPertici\GutenbergMarkup\Blocks
 */

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\Block\SelfClosingBlockSupportTrait;

/**
 * Button Gutenberg Block implementation.
 *
 * Supports the common button use case with content, URL, target and rel.
 *
 * @since 1.0.0
 */
class ButtonBlock extends BlockMarkup {

	use SelfClosingBlockSupportTrait;

	/**
	 * Button label/content.
	 *
	 * @var string
	 */
	protected string $content;

	/**
	 * Button URL.
	 *
	 * @var string
	 */
	protected string $url;

	/**
	 * Optional anchor target.
	 *
	 * @var string|null
	 */
	protected ?string $linkTarget = null;

	/**
	 * Optional anchor rel attribute.
	 *
	 * @var string|null
	 */
	protected ?string $rel = null;

	/**
	 * Constructor.
	 *
	 * @param string $content Button label/content.
	 * @param string $url     Button URL.
	 */
	public function __construct( string $content, string $url ) {
		$this->content = $content;
		$this->url     = $url;

		parent::__construct(
			blockName: 'core/button',
			blockAttributes: array(),
			wrapper: ''
		);
	}

	/**
	 * Get or set button content.
	 *
	 * @param string|null $content Optional content to set.
	 * @return string|self
	 */
	public function content( ?string $content = null ) {
		if ( null === $content ) {
			return $this->content;
		}

		$this->content = $content;
		return $this;
	}

	/**
	 * Get or set button URL.
	 *
	 * @param string|null $url Optional URL to set.
	 * @return string|self
	 */
	public function url( ?string $url = null ) {
		if ( null === $url ) {
			return $this->url;
		}

		$this->url = $url;
		return $this;
	}

	/**
	 * Get or set the link target.
	 *
	 * @param string|null $target Optional target to set.
	 * @return string|self|null
	 */
	public function linkTarget( ?string $target = null ) {
		if ( null === $target ) {
			return $this->linkTarget;
		}

		$this->linkTarget = $target;
		return $this;
	}

	/**
	 * Get or set the link rel attribute.
	 *
	 * @param string|null $rel Optional rel to set.
	 * @return string|self|null
	 */
	public function rel( ?string $rel = null ) {
		if ( null === $rel ) {
			return $this->rel;
		}

		$this->rel = $rel;
		return $this;
	}

	/**
	 * Open or close the button in a new tab.
	 *
	 * @param bool|null $open Optional toggle value.
	 * @return bool|self
	 */
	public function openInNewTab( ?bool $open = null ) {
		if ( null === $open ) {
			return '_blank' === $this->linkTarget;
		}

		if ( $open ) {
			$this->linkTarget = '_blank';
		} elseif ( '_blank' === $this->linkTarget ) {
			$this->linkTarget = null;
		}

		return $this;
	}

	/**
	 * Build block attributes before rendering.
	 *
	 * @return void
	 */
	protected function build(): void {
		$attributes = $this->blockAttributes();
		$attributes['text'] = $this->content;
		$attributes['url']  = $this->url;

		if ( null !== $this->linkTarget && '' !== $this->linkTarget ) {
			$attributes['linkTarget'] = $this->linkTarget;
		} else {
			unset( $attributes['linkTarget'] );
		}

		if ( null !== $this->rel && '' !== $this->rel ) {
			$attributes['rel'] = $this->rel;
		} else {
			unset( $attributes['rel'] );
		}

		$this->setBlockAttributes( $attributes, false );
	}

	/**
	 * Render the complete Gutenberg button block.
	 *
	 * @return string
	 */
	public function render(): string {
		$this->build();

		$blockAttributes = $this->blockAttributes();
		$wrapperClasses  = trim( 'wp-block-button ' . (string) ( $blockAttributes['className'] ?? '' ) );
		$anchorClasses   = 'wp-block-button__link wp-element-button';
		$anchorAttrs     = sprintf( ' href="%s"', self::escapeAttribute( $this->url ) );

		if ( null !== $this->linkTarget && '' !== $this->linkTarget ) {
			$anchorAttrs .= sprintf( ' target="%s"', self::escapeAttribute( $this->linkTarget ) );
		}

		if ( null !== $this->rel && '' !== $this->rel ) {
			$anchorAttrs .= sprintf( ' rel="%s"', self::escapeAttribute( $this->rel ) );
		}

		$html = sprintf(
			'<div class="%1$s"><a class="%2$s"%3$s>%4$s</a></div>',
			self::escapeAttribute( $wrapperClasses ),
			self::escapeAttribute( $anchorClasses ),
			$anchorAttrs,
			$this->renderContent()
		);

		return $this->blockComments()->wrapContent( $html );
	}

	/**
	 * Print the complete Gutenberg button block.
	 *
	 * @return void
	 */
	public function print(): void {
		echo $this->render();
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

		if ( isset( $attributes['text'] ) ) {
			$this->content( (string) $attributes['text'] );
		}

		if ( isset( $attributes['url'] ) ) {
			$this->url( (string) $attributes['url'] );
		}

		if ( isset( $attributes['linkTarget'] ) ) {
			$this->linkTarget( (string) $attributes['linkTarget'] );
		}

		if ( isset( $attributes['rel'] ) ) {
			$this->rel( (string) $attributes['rel'] );
		}

		if ( array_key_exists( 'openInNewTab', $attributes ) ) {
			$this->openInNewTab( (bool) $attributes['openInNewTab'] );
		}

		return $this;
	}

	/**
	 * Escape an HTML attribute value.
	 *
	 * @param string $value Raw attribute value.
	 * @return string
	 */
	private static function escapeAttribute( string $value ): string {
		return htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}

	/**
	 * Render button content safely.
	 *
	 * Button content is always rendered as escaped plain text.
	 *
	 * @return string
	 */
	private function renderContent(): string {
		return htmlspecialchars( $this->content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}
}
