<?php
/**
 * Post content helper for Gutenberg parsed tree manipulations.
 *
 * @package MaxPertici\GutenbergMarkup
 */

namespace MaxPertici\GutenbergMarkup;

use MaxPertici\Markup\Markup;
use MaxPertici\Markup\MarkupCollection;

/**
 * Helper API to manipulate Gutenberg post content as a parsed blocks tree.
 *
 * Provides fluent rendering and conversion helpers from raw Gutenberg
 * markup or parse_blocks()-style arrays.
 */
class PostContent extends Markup {

	/**
	 * Default local parser mapping used when converting to block objects.
	 *
	 * @var array<string, callable|string>
	 */
	private array $blockParsers = [];

	/**
	 * @param string|array<int, array<string, mixed>> $contentOrBlocks
	 *        - string: raw Gutenberg markup.
	 *        - array: parsed blocks tree (same structure as parse_blocks()).
	 * @param array<string, callable|string>          $blockParsers Local parser mapping.
	 */
	public function __construct( string|array $contentOrBlocks, array $blockParsers = [] ) {
		$this->blockParsers = $blockParsers;
		$parsedBlocks = is_string( $contentOrBlocks )
			? self::parseMarkupToBlocksTree( $contentOrBlocks )
			: self::normalizeParsedBlocks( $contentOrBlocks );

		parent::__construct(
			'',
			[],
			[],
			'',
			self::createMarkupChildrenFromParsedBlocks( $parsedBlocks )
		);
	}

	/**
	 * Return parsed Gutenberg blocks tree.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function parsedBlocks(): array {
		return self::parseMarkupToBlocksTree( $this->toMarkup() );
	}

	/**
	 * Convert parsed tree to nested BlockMarkup/string structure.
	 *
	 * @return array<int, BlockMarkup|string>
	 */
	public function toBlockMarkup(): array {
		return self::createMarkupChildrenFromParsedBlocks( $this->parsedBlocks() );
	}

	/**
	 * Convert parsed tree to a Markup collection.
	 *
	 * @return MarkupCollection
	 */
	public function toBlockMarkupCollection(): MarkupCollection {
		return MarkupCollection::make( $this->toBlockMarkup() );
	}

	/**
	 * Convert parsed tree to a typed blocks collection.
	 *
	 * @param array<string, callable|string>|null $blockParsers Local parser mapping override.
	 * @return MarkupCollection
	 */
	public function toBlocksCollection( ?array $blockParsers = null ): MarkupCollection {
		return MarkupCollection::make( $this->toBlocks( $blockParsers ) );
	}

	/**
	 * Convert parsed tree to typed block objects / markup fallback strings.
	 *
	 * @param array<string, callable|string>|null $blockParsers Local parser mapping override.
	 * @return array<int, object|string>
	 */
	public function toBlocks( ?array $blockParsers = null ): array {
		$resolvers = null === $blockParsers ? $this->blockParsers : $blockParsers;

		return BlockFactory::parsePostContent( $this->toMarkup(), $resolvers );
	}

	/**
	 * Convert current parsed tree back to Gutenberg markup.
	 *
	 * @param array<string, callable|string>|null $blockParsers Local parser mapping override used by fallback renderer.
	 * @return string
	 */
	public function toMarkup( ?array $blockParsers = null ): string {
		if ( null === $blockParsers ) {
			return parent::render();
		}

		return self::renderValuesToString( $this->toBlocks( $blockParsers ) );
	}

	/**
	 * Build a new PostContent instance from already updated renderable blocks.
	 *
	 * @param array<int, object|string>|MarkupCollection $blocks Renderable block objects or fallback strings.
	 * @param array<string, callable|string>|null $blockParsers Local parser mapping override.
	 * @return self
	 */
	public function withBlocks( array|MarkupCollection $blocks, ?array $blockParsers = null ): self {
		$resolvers = null === $blockParsers ? $this->blockParsers : $blockParsers;
		$values    = is_array( $blocks ) ? $blocks : iterator_to_array( $blocks );

		return new self( self::renderValuesToString( $values ), $resolvers );
	}

	/**
	 * Print current Gutenberg markup (echo mode).
	 *
	 * @return void
	 */
	public function print(): void {
		echo $this->toMarkup();
	}

	/**
	 * Render current markup through WordPress do_blocks() when available.
	 *
	 * @return string
	 */
	public function renderBlocks(): string {
		$markup = $this->toMarkup();

		if ( \function_exists( 'do_blocks' ) ) {
			// @phpstan-ignore-next-line
			return \do_blocks( $markup );
		}

		return $markup;
	}

	/**
	 * Print rendered blocks output (echo mode).
	 *
	 * @return void
	 */
	public function printBlocks(): void {
		echo $this->renderBlocks();
	}

	/**
	 * @param string $postContent Raw Gutenberg markup.
	 * @return array<int, array<string, mixed>>
	 */
	private static function parseMarkupToBlocksTree( string $postContent ): array {
		if ( BlockFactory::isNativeParserAvailable() ) {
			$parser = 'parse_blocks';

			return self::normalizeParsedBlocks( $parser( $postContent ) );
		}

		// Fallback as one unparsed/raw chunk when parse_blocks() is unavailable.
		return array(
			array(
				'blockName' => null,
				'attrs' => array(),
				'innerBlocks' => array(),
				'innerHTML' => $postContent,
				'innerContent' => array( $postContent ),
			),
		);
	}

	/**
	 * @param array<int, mixed> $parsedBlocks Parsed blocks payload.
	 * @return array<int, array<string, mixed>>
	 */
	private static function normalizeParsedBlocks( array $parsedBlocks ): array {
		$normalized = [];

		foreach ( $parsedBlocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}

			$normalized[] = $block;
		}

		return $normalized;
	}

	/**
	 * @param mixed $value Value to render.
	 * @return string
	 */
	private static function renderValueToString( mixed $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( is_object( $value ) && \method_exists( $value, 'render' ) ) {
			return (string) $value->render();
		}

		return '';
	}

	/**
	 * Render a list of values to Gutenberg markup.
	 *
	 * @param array<int, mixed> $values Values to render.
	 * @return string
	 */
	private static function renderValuesToString( array $values ): string {
		$output = '';

		foreach ( $values as $value ) {
			$output .= self::renderValueToString( $value );
		}

		return $output;
	}

	/**
	 * @param array<int, array<string, mixed>> $parsedBlocks Parsed tree.
	 * @return array<int, BlockMarkup|string>
	 */
	private static function createMarkupChildrenFromParsedBlocks( array $parsedBlocks ): array {
		$children = [];

		foreach ( $parsedBlocks as $parsedBlock ) {
			if ( ! is_array( $parsedBlock ) ) {
				continue;
			}

			$children[] = self::createMarkupChildFromParsedBlock( $parsedBlock );
		}

		return $children;
	}

	/**
	 * @param array<string, mixed> $parsedBlock Parsed block payload.
	 * @return BlockMarkup|string
	 */
	private static function createMarkupChildFromParsedBlock( array $parsedBlock ): BlockMarkup|string {
		$blockName = isset( $parsedBlock['blockName'] ) && is_string( $parsedBlock['blockName'] ) ? $parsedBlock['blockName'] : '';

		if ( '' === $blockName ) {
			$children = self::createMarkupChildrenFromInnerContent( $parsedBlock );
			$output   = '';

			foreach ( $children as $child ) {
				$output .= self::renderValueToString( $child );
			}

			return $output;
		}

		$attrs = is_array( $parsedBlock['attrs'] ?? null ) ? $parsedBlock['attrs'] : [];

		return new BlockMarkup(
			blockName: $blockName,
			blockAttributes: $attrs,
			isSelfClosing: ! self::hasRenderableInnerContent( $parsedBlock ),
			wrapper: '',
			children: self::createMarkupChildrenFromInnerContent( $parsedBlock )
		);
	}

	/**
	 * @param array<string, mixed> $parsedBlock Parsed block payload.
	 * @return array<int, BlockMarkup|string>
	 */
	private static function createMarkupChildrenFromInnerContent( array $parsedBlock ): array {
		$innerContent = is_array( $parsedBlock['innerContent'] ?? null ) ? $parsedBlock['innerContent'] : [];
		$innerBlocks  = self::normalizeParsedBlocks( is_array( $parsedBlock['innerBlocks'] ?? null ) ? $parsedBlock['innerBlocks'] : [] );

		if ( empty( $innerContent ) ) {
			if ( ! empty( $innerBlocks ) ) {
				return self::createMarkupChildrenFromParsedBlocks( $innerBlocks );
			}

			$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
			if ( '' === $innerHtml ) {
				return [];
			}

			return [ $innerHtml ];
		}

		$children   = [];
		$childIndex = 0;

		foreach ( $innerContent as $chunk ) {
			if ( null === $chunk ) {
				$child = $innerBlocks[ $childIndex ] ?? null;
				if ( is_array( $child ) ) {
					$children[] = self::createMarkupChildFromParsedBlock( $child );
				}
				++$childIndex;
				continue;
			}

			$children[] = (string) $chunk;
		}

		return $children;
	}

	/**
	 * @param array<string, mixed> $parsedBlock Parsed block payload.
	 * @return bool
	 */
	private static function hasRenderableInnerContent( array $parsedBlock ): bool {
		$innerBlocks = is_array( $parsedBlock['innerBlocks'] ?? null ) ? $parsedBlock['innerBlocks'] : [];
		if ( ! empty( $innerBlocks ) ) {
			return true;
		}

		$innerContent = is_array( $parsedBlock['innerContent'] ?? null ) ? $parsedBlock['innerContent'] : [];
		foreach ( $innerContent as $chunk ) {
			if ( null === $chunk ) {
				return true;
			}

			if ( '' !== trim( (string) $chunk ) ) {
				return true;
			}
		}

		return '' !== trim( (string) ( $parsedBlock['innerHTML'] ?? '' ) );
	}

}
