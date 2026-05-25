<?php
/**
 * Block Markup for Gutenberg Blocks
 *
 * Base class for Gutenberg block markup generation.
 *
 * @package MaxPertici\GutenbergMarkup
 */

namespace MaxPertici\GutenbergMarkup;

use MaxPertici\Markup\Markup;

/**
 * Base class for Gutenberg block markup generation.
 *
 * Extends the Markup class to add Gutenberg block comment support.
 * Automatically wraps the generated markup with appropriate Gutenberg block comments.
 * This class facilitates the creation of Gutenberg blocks programmatically by handling
 * the block comment syntax required by the WordPress block editor.
 *
 * Example usage:
 * ```php
 * $block = new BlockMarkup(
 *     'core/paragraph',
 *     ['align' => 'center'],
 *     false,
 *     '<p>{children}</p>'
 * );
 * ```
 *
 * @since 1.0.0
 */
class BlockMarkup extends Markup {

	/**
	 * The block name (e.g., 'core/paragraph', 'acf/my-block').
	 *
	 * This property stores the full block identifier used by Gutenberg,
	 * including the namespace and block name separated by a slash.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $blockName;

	/**
	 * The block attributes.
	 *
	 * An associative array containing all block attributes that will be
	 * serialized into the Gutenberg block comment syntax (JSON format).
	 * These attributes are used by the block editor to maintain state.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $blockAttributes;

	/**
	 * Whether this is a self-closing block (no inner content).
	 *
	 * When true, the block will be rendered as a self-closing comment
	 * without opening and closing tags. This is useful for blocks that
	 * don't contain any inner content or children.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $isSelfClosing;

	/**
	 * The BlockComments instance.
	 *
	 * This instance handles the generation of Gutenberg block comment syntax,
	 * including opening comments, closing comments, and self-closing comments.
	 *
	 * @since 1.0.0
	 * @var BlockComments
	 */
	private BlockComments $blockComments;

	/**
	 * Guard flag to prevent recursive hydration loops.
	 *
	 * @var bool
	 */
	private bool $isHydrating = false;

	/**
	 * Constructor.
	 *
	 * Initializes a new BlockMarkup instance with Gutenberg block support.
	 * Sets up the parent Markup properties and creates a BlockComments instance
	 * to handle the Gutenberg comment syntax.
	 *
	 * @since 1.0.0
	 *
	 * @param string $blockName         The Gutenberg block name (e.g., 'core/paragraph', 'acf/hero-block').
	 * @param array  $blockAttributes   Optional. Block attributes as key-value pairs. Default empty array.
	 * @param bool   $isSelfClosing     Optional. Whether block is self-closing (no inner content). Default false.
	 * @param string $wrapper           Optional. HTML wrapper template with {children} placeholder. Default empty string.
	 * @param array  $wrapperClass      Optional. CSS classes to apply to the wrapper element. Default empty array.
	 * @param array  $wrapperAttributes Optional. HTML attributes for the wrapper element. Default empty array.
	 * @param string $childrenWrapper   Optional. HTML wrapper template for children elements. Default empty string.
	 * @param array  $children          Optional. Array of child Markup objects or strings. Default empty array.
	 */
	public function __construct(
		string $blockName,
		array $blockAttributes = [],
		bool $isSelfClosing = false,
		string $wrapper = '',
		array $wrapperClass = [],
		array $wrapperAttributes = [],
		string $childrenWrapper = '',
		array $children = []
	) {
		// Initialize parent Markup
		parent::__construct(
			$wrapper,
			$wrapperClass,
			$wrapperAttributes,
			$childrenWrapper,
			$children
		);

		// Initialize block-specific properties
		$this->blockName       = $blockName;
		$this->blockAttributes = $blockAttributes;
		$this->isSelfClosing   = $isSelfClosing;

		// Create BlockComments instance
		$this->blockComments = new BlockComments( $blockName, $blockAttributes );
	}

	/**
	 * Build runtime state before rendering.
	 *
	 * Block classes can override this hook to compute wrapper/classes/attrs.
	 *
	 * @return void
	 */
	protected function build(): void {}

	/**
	 * Gets the complete block markup with Gutenberg comments.
	 *
	 * Wraps the parent markup with appropriate Gutenberg block comments.
	 * For self-closing blocks, returns only the self-closing comment.
	 * For regular blocks, wraps the inner HTML content with opening and closing block comments.
	 *
	 * This method overrides the parent render() to add Gutenberg-specific formatting.
	 *
	 * @since 1.0.0
	 *
	 * @return string The complete block markup including Gutenberg comment syntax.
	 */
	public function render(): string {
		$this->build();

		// Update the BlockComments instance with current attributes
		$this->blockComments = new BlockComments( $this->blockName, $this->blockAttributes );

		// If self-closing block, return only the comment
		if ( $this->isSelfClosing ) {
			return $this->blockComments->selfClosingComment();
		}

		// Get the inner markup from parent class
		$innerMarkup = parent::render();

		// Wrap with block comments
		return $this->blockComments->wrapContent( $innerMarkup );
	}

	/**
	 * Prints the complete block markup with Gutenberg comments (echo mode).
	 *
	 * Outputs the block with appropriate Gutenberg block comments directly to the buffer.
	 * This method is optimized for performance by echoing content in streaming mode
	 * instead of building the entire string in memory first.
	 *
	 * For self-closing blocks, echoes only the self-closing comment.
	 * For regular blocks, echoes the opening comment, inner content, and closing comment separately.
	 *
	 * This method overrides the parent print() to add Gutenberg-specific formatting.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print(): void {
		$this->build();

		// Update the BlockComments instance with current attributes
		$this->blockComments = new BlockComments( $this->blockName, $this->blockAttributes );

		// If self-closing block, echo only the comment
		if ( $this->isSelfClosing ) {
			echo $this->blockComments->selfClosingComment();
			return;
		}

		// Echo opening block comment
		echo $this->blockComments->openingComment();

		// Print inner content using parent's print method
		parent::print();

		// Echo closing block comment
		echo $this->blockComments->closingComment();
	}

	/**
	 * Gets the block name.
	 *
	 * Returns the fully qualified block name including namespace
	 * (e.g., 'core/paragraph', 'acf/hero-block').
	 *
	 * @since 1.0.0
	 *
	 * @return string The block name with namespace.
	 */
	public function blockName(): string {
		return $this->blockName;
	}

	/**
	 * Gets the block attributes.
	 *
	 * Returns the complete array of block attributes that will be
	 * serialized into the Gutenberg block comment. These attributes
	 * maintain the block's state in the editor.
	 *
	 * @since 1.0.0
	 *
	 * @return array The block attributes as an associative array.
	 */
	public function blockAttributes(): array {
		return $this->blockAttributes;
	}

	/**
	 * Return whether this block can contain children.
	 *
	 * Gutenberg block validity is business-level, but markup composition is generic.
	 * Leaf-like block subclasses may override this and return false to reject
	 * child mutation APIs (`addChild`, `addChildren`, `setChildren`) semantically.
	 * When false, mutation methods return early and keep current children unchanged.
	 * This guard applies to mutation APIs only; constructor-provided children are
	 * preserved as-is.
	 *
	 * @return bool
	 */
	public function supportsChildren(): bool {
		return true;
	}

	/**
	 * Add one child block/content.
	 *
	 * @param object|string $child Child block or raw string content.
	 * @return self
	 */
	public function addChild( object|string $child ): self {
		if ( ! $this->supportsChildren() ) {
			return $this;
		}

		if ( ! $this->isValidChild( $child ) ) {
			return $this;
		}

		$this->children[] = $child;
		$this->afterChildrenMutation();

		return $this;
	}

	/**
	 * Add multiple child blocks/content.
	 *
	 * @param array<int, object|string> $children Children to append.
	 * @return self
	 */
	public function addChildren( array $children ): self {
		if ( ! $this->supportsChildren() ) {
			return $this;
		}

		$validChildren = array_values(
			array_filter(
				$children,
				fn ( mixed $child ): bool => $this->isValidChild( $child )
			)
		);

		if ( ! empty( $validChildren ) ) {
			$this->children = array_merge( $this->children, $validChildren );
			$this->afterChildrenMutation();
		}

		return $this;
	}

	/**
	 * Replace all children.
	 *
	 * @param array<int, object|string> $children Child block/content list.
	 * @return self
	 */
	public function setChildren( array $children ): self {
		if ( ! $this->supportsChildren() ) {
			return $this;
		}

		$this->children = array_values(
			array_filter(
				$children,
				fn ( mixed $child ): bool => $this->isValidChild( $child )
			)
		);

		$this->afterChildrenMutation();

		return $this;
	}

	/**
	 * Sets or updates block attributes.
	 *
	 * Allows updating the block attributes after instantiation.
	 * When merging, new attributes are combined with existing ones,
	 * with new values overwriting existing keys.
	 *
	 * After updating attributes, the internal BlockComments instance
	 * is recreated to reflect the new attribute values in the comment syntax.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes The attributes to set or merge as key-value pairs.
	 * @param bool  $merge      Optional. Whether to merge with existing attributes (true)
	 *                          or replace them entirely (false). Default true.
	 * @return void
	 */
	public function setBlockAttributes( array $attributes, bool $merge = true ): void {
		if ( $merge ) {
			$this->blockAttributes = array_merge( $this->blockAttributes, $attributes );
		} else {
			$this->blockAttributes = $attributes;
		}

		$this->applyAttributeHydration( $attributes );

		// Update the BlockComments instance
		$this->blockComments = new BlockComments( $this->blockName, $this->blockAttributes );
	}

	/**
	 * Run shared state updates after child mutations.
	 *
	 * @return void
	 */
	protected function afterChildrenMutation(): void {
		if ( ! empty( $this->children ) ) {
			$this->isSelfClosing = false;
		}
	}

	/**
	 * Validate one child value accepted by the Markup tree.
	 *
	 * Accepts only `string` and `\Stringable` object values. Mutation APIs silently
	 * ignore non-compatible values.
	 *
	 * @param mixed $child Candidate child value.
	 * @return bool
	 */
	private function isValidChild( mixed $child ): bool {
		if ( is_string( $child ) ) {
			return true;
		}

		if ( ! is_object( $child ) ) {
			return false;
		}

		return $child instanceof \Stringable;
	}

	/**
	 * Hydrate runtime state from parsed Gutenberg attributes.
	 *
	 * This keeps object-level search fields (`wrapperClass`, `wrapperAttributes`)
	 * synchronized so `findByClass()` and `findByAttribute()` work reliably after
	 * parsing content with BlockFactory.
	 *
	 * @since 1.1.0
	 *
	 * @param array $attributes Parsed block attributes.
	 * @param bool  $merge Optional. Merge or replace base block attributes. Default false.
	 * @return self
	 */
	public function hydrate( array $attributes, bool $merge = false ): self {
		$this->setBlockAttributes( $attributes, $merge );

		return $this;
	}

	/**
	 * Prepare current block (and optionally descendants) for finder queries.
	 *
	 * Calls `build()` when present to materialize computed classes/attributes, then
	 * recursively prepares child blocks.
	 *
	 * @since 1.1.0
	 *
	 * @param bool $deep Optional. Prepare descendants recursively. Default true.
	 * @return self
	 */
	public function prepareForFind( bool $deep = true ): self {
		if ( method_exists( $this, 'build' ) ) {
			$this->build();
		}

		if ( ! $deep ) {
			return $this;
		}

		foreach ( $this->getChildren() as $child ) {
			if ( $child instanceof self ) {
				$child->prepareForFind( true );
			}
		}

		return $this;
	}

	/**
	 * Apply common Gutenberg attrs into object runtime state via trait setters.
	 *
	 * @param array $attributes Parsed block attributes.
	 * @return void
	 */
	protected function applyAttributeHydration( array $attributes ): void {
		if ( $this->isHydrating ) {
			return;
		}

		$this->isHydrating = true;

		try {
			$this->hydrateByMethodIfCallable( 'anchor', $attributes['anchor'] ?? null );
			$this->hydrateByMethodIfCallable( 'customClass', $attributes['className'] ?? null );
			$this->hydrateByMethodIfCallable( 'textColor', $attributes['textColor'] ?? null );
			$this->hydrateByMethodIfCallable( 'backgroundColor', $attributes['backgroundColor'] ?? null );
			$this->hydrateByMethodIfCallable( 'fontSize', $attributes['fontSize'] ?? null );
			$this->hydrateByMethodIfCallable( 'hasAlphaChannelOpacity', $attributes['hasAlphaChannelOpacity'] ?? null );

			if ( array_key_exists( 'textAlign', $attributes ) && is_string( $attributes['textAlign'] ) ) {
				$this->hydrateByMethodIfCallable( 'textAlign', $attributes['textAlign'] );
			}

			if ( array_key_exists( 'align', $attributes ) && is_string( $attributes['align'] ) ) {
				if ( method_exists( $this, 'textAlign' ) ) {
					$this->textAlign( (string) $attributes['align'] );
				} else {
					$this->hydrateByMethodIfCallable( 'align', $attributes['align'] );
				}
			}

			$this->hydrateTypographyAndStyleAttributes( $attributes );
		} finally {
			$this->isHydrating = false;
		}
	}

	/**
	 * Hydrate one attribute via block method when callable and scalar-compatible.
	 *
	 * @param string $method Method name.
	 * @param mixed  $value Attribute value.
	 * @return void
	 */
	private function hydrateByMethodIfCallable( string $method, mixed $value ): void {
		if ( null === $value || ! method_exists( $this, $method ) ) {
			return;
		}

		if ( ! is_scalar( $value ) ) {
			return;
		}

		try {
			$this->{$method}( $value );
		} catch ( \Throwable $e ) {
			// Best-effort hydration: ignore non-compatible setters.
		}
	}

	/**
	 * Hydrate style-derived attributes when present.
	 *
	 * @param array $attributes Parsed block attributes.
	 * @return void
	 */
	private function hydrateTypographyAndStyleAttributes( array $attributes ): void {
		$style = is_array( $attributes['style'] ?? null ) ? $attributes['style'] : [];
		if ( empty( $style ) ) {
			return;
		}

		$typography = is_array( $style['typography'] ?? null ) ? $style['typography'] : [];
		$this->hydrateByMethodIfCallable( 'fontStyle', $typography['fontStyle'] ?? null );
		$this->hydrateByMethodIfCallable( 'fontWeight', $typography['fontWeight'] ?? null );
		$this->hydrateByMethodIfCallable( 'letterSpacing', $typography['letterSpacing'] ?? null );
		$this->hydrateByMethodIfCallable( 'lineHeight', $typography['lineHeight'] ?? null );
		$this->hydrateByMethodIfCallable( 'textDecoration', $typography['textDecoration'] ?? null );
		$this->hydrateByMethodIfCallable( 'textTransform', $typography['textTransform'] ?? null );

		$color = is_array( $style['color'] ?? null ) ? $style['color'] : [];
		$this->hydrateByMethodIfCallable( 'customTextColor', $color['text'] ?? null );
		$this->hydrateByMethodIfCallable( 'customBackgroundColor', $color['background'] ?? null );

		$linkTextColor = $style['elements']['link']['color']['text'] ?? null;
		$this->hydrateByMethodIfCallable( 'customLinkColor', $linkTextColor );
	}

	/**
	 * Gets the BlockComments instance.
	 *
	 * Provides access to the internal BlockComments object that handles
	 * the generation of Gutenberg block comment syntax. This can be useful
	 * for advanced manipulation or when you need direct access to comment
	 * generation methods.
	 *
	 * @since 1.0.0
	 *
	 * @return BlockComments The BlockComments instance used by this block.
	 */
	public function blockComments(): BlockComments {
		return $this->blockComments;
	}

	/**
	 * Gets the block markup and processes it through WordPress do_blocks().
	 *
	 * This method first renders the block markup (with Gutenberg comments) using render(),
	 * then processes it through WordPress's do_blocks() function to transform the block
	 * markup into final rendered HTML output.
	 *
	 * This is similar to print() but returns the fully processed HTML instead of echoing it.
	 * It combines the block generation with WordPress block rendering in one step.
	 *
	 * Example usage:
	 * ```php
	 * $block = new BlockMarkup(
	 *     'core/paragraph',
	 *     ['align' => 'center'],
	 *     false,
	 *     '<p>{children}</p>'
	 * );
	 * $html = $block->renderBlocks();
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @return string The fully rendered HTML output after do_blocks() processing.
	 */
	public function renderBlocks(): string {
		// Get the block markup with Gutenberg comments
		$markup = $this->render();

		// Process through WordPress do_blocks if available
		if ( \function_exists( 'do_blocks' ) ) {
			// @phpstan-ignore-next-line
			return \do_blocks( $markup );
		}

		// Fallback if do_blocks is not available
		return $markup;
	}

	/**
	 * Prints the fully rendered block HTML (echo mode).
	 *
	 * Outputs the block markup after processing through WordPress's do_blocks() function.
	 * This method echoes the result of renderBlocks() directly to the output buffer.
	 *
	 * This is the echo equivalent of renderBlocks(), similar to how print() is
	 * the echo equivalent of render().
	 *
	 * Example usage:
	 * ```php
	 * $block = new BlockMarkup(
	 *     'core/paragraph',
	 *     ['align' => 'center'],
	 *     false,
	 *     '<p>{children}</p>'
	 * );
	 * $block->printBlocks();
	 * ```
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function printBlocks(): void {
		echo $this->renderBlocks();
	}

	/**
	 * Escape plain text for safe HTML output.
	 *
	 * @param string $value Raw text.
	 * @return string
	 */
	protected static function escapeText( string $value ): string {
		if ( \function_exists( 'esc_html' ) ) {
			// @phpstan-ignore-next-line
			return (string) \esc_html( $value );
		}

		return htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}

	/**
	 * Escape an HTML attribute value.
	 *
	 * @param string $value Raw attribute value.
	 * @return string
	 */
	protected static function escapeAttribute( string $value ): string {
		if ( \function_exists( 'esc_attr' ) ) {
			// @phpstan-ignore-next-line
			return (string) \esc_attr( $value );
		}

		return htmlspecialchars( $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8' );
	}

	/**
	 * Sanitize URL values with a safe protocol allowlist fallback.
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	protected static function sanitizeUrl( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		if ( \function_exists( 'esc_url_raw' ) ) {
			// @phpstan-ignore-next-line
			$sanitized = (string) \esc_url_raw( $url );
			return '' === $sanitized ? '' : $sanitized;
		}

		if (
			str_starts_with( $url, '/' )
			|| str_starts_with( $url, '#' )
			|| str_starts_with( $url, '?' )
		) {
			return $url;
		}

		$parsed = parse_url( $url );
		if ( false === $parsed ) {
			return '';
		}

		$scheme = strtolower( (string) ( $parsed['scheme'] ?? '' ) );
		if ( '' === $scheme ) {
			return $url;
		}

		$allowedSchemes = [ 'http', 'https', 'mailto', 'tel' ];

		return in_array( $scheme, $allowedSchemes, true ) ? $url : '';
	}

	/**
	 * Sanitize and escape URL for HTML attributes.
	 *
	 * @param string $url Raw URL.
	 * @return string
	 */
	protected static function escapeUrlAttribute( string $url ): string {
		$sanitized = self::sanitizeUrl( $url );

		if ( \function_exists( 'esc_url' ) ) {
			// @phpstan-ignore-next-line
			return (string) \esc_url( $sanitized );
		}

		return self::escapeAttribute( $sanitized );
	}

}
