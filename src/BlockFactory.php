<?php
/**
 * Block Factory
 *
 * Parse Gutenberg post content and create block objects.
 *
 * @package MaxPertici\GutenbergMarkup
 */

namespace MaxPertici\GutenbergMarkup;

use MaxPertici\GutenbergMarkup\Blocks\ColumnBlock;
use MaxPertici\GutenbergMarkup\Blocks\ColumnsBlock;
use MaxPertici\GutenbergMarkup\Blocks\FileBlock;
use MaxPertici\GutenbergMarkup\Blocks\GroupBlock;
use MaxPertici\GutenbergMarkup\Blocks\HeadingBlock;
use MaxPertici\GutenbergMarkup\Blocks\ImageBlock;
use MaxPertici\GutenbergMarkup\Blocks\ListBlock;
use MaxPertici\GutenbergMarkup\Blocks\ListItemBlock;
use MaxPertici\GutenbergMarkup\Blocks\ParagraphBlock;
use MaxPertici\GutenbergMarkup\Blocks\PullquoteBlock;
use MaxPertici\GutenbergMarkup\Blocks\QuoteBlock;
use MaxPertici\GutenbergMarkup\Blocks\SeparatorBlock;

class BlockFactory {

	/**
	 * Global custom parser mapping.
	 *
	 * Each resolver can be:
	 * - a callable: fn(array $parsedBlock, array $attrs): object|string|null
	 * - a class-string implementing either a static fromParsedBlock(array $parsedBlock)
	 *   method or a zero-argument constructor.
	 *
	 * @var array<string, callable|string>
	 */
	private static array $customBlockParsers = [];

	/**
	 * Parse post content and create blocks.
	 *
	 * Supported Gutenberg blocks are converted to dedicated classes.
	 * Unsupported blocks are returned as simple markup strings.
	 *
	 * @param string $postContent Raw Gutenberg post content.
	 * @return array<int, object|string>
	 */
	public static function parsePostContent( string $postContent, array $blockParsers = [] ): array {
		if ( ! \function_exists( 'parse_blocks' ) ) {
			return [ $postContent ];
		}

		$parsedBlocks = \parse_blocks( $postContent );
		$blocks       = [];

		foreach ( $parsedBlocks as $parsedBlock ) {
			$block = self::createFromParsedBlock( $parsedBlock, $blockParsers );
			if ( null !== $block && '' !== $block ) {
				$blocks[] = $block;
			}
		}

		return $blocks;
	}

	/**
	 * Register a global block parser or class mapping.
	 *
	 * @param string          $blockName Gutenberg block name (e.g. core/paragraph).
	 * @param callable|string $resolver Parser callable or class-string.
	 * @return void
	 */
	public static function registerBlockParser( string $blockName, callable|string $resolver ): void {
		self::$customBlockParsers[ $blockName ] = $resolver;
	}

	/**
	 * Clear all global custom block parsers.
	 *
	 * @return void
	 */
	public static function clearBlockParsers(): void {
		self::$customBlockParsers = [];
	}

	/**
	 * Create a block instance (or markup fallback) from parse_blocks() output.
	 *
	 * @param array $parsedBlock A parsed block item.
	 * @return object|string|null
	 */
	private static function createFromParsedBlock( array $parsedBlock, array $blockParsers = [] ) {
		$blockName = $parsedBlock['blockName'] ?? null;
		$attrs     = is_array( $parsedBlock['attrs'] ?? null ) ? $parsedBlock['attrs'] : [];

		if ( empty( $blockName ) ) {
			return self::buildStringContentFromParsedBlock( $parsedBlock, $blockParsers );
		}

		$block = self::createMappedBlock( $blockName, $attrs, $parsedBlock, $blockParsers );

		if ( null !== $block ) {
			return $block;
		}

		$block = self::createSupportedBlock( $blockName, $attrs, $parsedBlock, $blockParsers );
		if ( null !== $block ) {
			return $block;
		}

		$block = self::createAutomaticBlock( $blockName, $attrs, $parsedBlock );
		if ( null !== $block ) {
			return $block;
		}

		return self::createSimpleMarkupBlock( $blockName, $attrs, $parsedBlock, $blockParsers );
	}

	/**
	 * Create a dedicated block instance for supported block names.
	 *
	 * @param string $blockName Block name from parse_blocks().
	 * @param array  $attrs Parsed block attributes.
	 * @param array  $parsedBlock Full parsed block payload.
	 * @return object|null
	 */
	private static function createSupportedBlock( string $blockName, array $attrs, array $parsedBlock, array $blockParsers = [] ): ?object {
		return match ( $blockName ) {
			'core/paragraph' => self::createParagraphBlock( $attrs, $parsedBlock ),
			'core/heading' => self::createHeadingBlock( $attrs, $parsedBlock ),
			'core/group' => self::createGroupBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/columns' => self::createColumnsBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/column' => self::createColumnBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/quote' => self::createQuoteBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/pullquote' => self::createPullquoteBlock( $attrs, $parsedBlock ),
			'core/list' => self::createListBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/list-item' => self::createListItemBlock( $attrs, $parsedBlock ),
			'core/separator' => self::createSeparatorBlock( $attrs ),
			'core/file' => self::createFileBlock( $attrs ),
			'core/image' => self::createImageBlock( $attrs ),
			default => null,
		};
	}

	/**
	 * Try mapped parsers from local and global mapping.
	 *
	 * @param string $blockName Block name.
	 * @param array  $attrs Block attributes.
	 * @param array  $parsedBlock Parsed block payload.
	 * @param array  $blockParsers Local parser mapping.
	 * @return object|string|null
	 */
	private static function createMappedBlock( string $blockName, array $attrs, array $parsedBlock, array $blockParsers = [] ) {
		if ( array_key_exists( $blockName, $blockParsers ) ) {
			return self::resolveMappedBlock( $blockParsers[ $blockName ], $parsedBlock, $attrs, $blockParsers );
		}

		if ( array_key_exists( $blockName, self::$customBlockParsers ) ) {
			return self::resolveMappedBlock( self::$customBlockParsers[ $blockName ], $parsedBlock, $attrs, $blockParsers );
		}

		return null;
	}

	/**
	 * Resolve one mapping entry to a parsed block instance.
	 *
	 * @param callable|string $resolver Resolver callback or class-string.
	 * @param array           $parsedBlock Parsed block payload.
	 * @param array           $attrs Block attributes.
	 * @return object|string|null
	 */
	private static function resolveMappedBlock( callable|string $resolver, array $parsedBlock, array $attrs, array $blockParsers = [] ) {
		if ( is_callable( $resolver ) ) {
			return $resolver( $parsedBlock, $attrs );
		}

		if ( is_string( $resolver ) ) {
			return self::createBlockFromClass( $resolver, $parsedBlock, $attrs, $blockParsers, true );
		}

		return null;
	}

	/**
	 * Automatic class resolution by Gutenberg block name convention.
	 *
	 * Example: core/list-item -> MaxPertici\GutenbergMarkup\Blocks\ListItemBlock
	 *
	 * @param string $blockName Block name.
	 * @param array  $attrs Block attributes.
	 * @param array  $parsedBlock Parsed block payload.
	 * @return object|null
	 */
	private static function createAutomaticBlock( string $blockName, array $attrs, array $parsedBlock ): ?object {
		$className = __NAMESPACE__ . '\\Blocks\\' . self::blockNameToClassSuffix( $blockName ) . 'Block';

		return self::createBlockFromClass( $className, $parsedBlock, $attrs );
	}

	/**
	 * Convert Gutenberg block name into class suffix.
	 *
	 * Example: core/list-item -> ListItem
	 *
	 * @param string $blockName Block name.
	 * @return string
	 */
	private static function blockNameToClassSuffix( string $blockName ): string {
		$parts     = explode( '/', $blockName, 2 );
		$blockSlug = $parts[1] ?? $parts[0];

		return str_replace( ' ', '', ucwords( str_replace( array( '-', '_' ), ' ', $blockSlug ) ) );
	}

	/**
	 * Create block instance from class.
	 *
	 * Supported strategies:
	 * - static fromParsedBlock(array $parsedBlock): object
	 * - zero-argument constructor + setBlockAttributes(array, false)
	 * - mapped class-string with first constructor argument typed as array receives parsed children
	 *
	 * @param string $className Class name.
	 * @param array  $parsedBlock Parsed block payload.
	 * @param array  $attrs Block attributes.
	 * @param array  $blockParsers Local parser mapping.
	 * @param bool   $hydrateConstructorFromParsedBlock Whether to hydrate constructor args from parsed block.
	 * @return object|null
	 */
	private static function createBlockFromClass( string $className, array $parsedBlock, array $attrs, array $blockParsers = [], bool $hydrateConstructorFromParsedBlock = false ): ?object {
		if ( ! class_exists( $className ) ) {
			return null;
		}

		try {
			if ( method_exists( $className, 'fromParsedBlock' ) ) {
				$instance = $className::fromParsedBlock( $parsedBlock );
				if ( is_object( $instance ) ) {
					return $instance;
				}
			}

			$reflection  = new \ReflectionClass( $className );
			$constructor = $reflection->getConstructor();
			$constructorArgs = [];
			if ( null !== $constructor && $hydrateConstructorFromParsedBlock ) {
				$constructorArgs = self::buildMappedConstructorArgs( $constructor, $parsedBlock, $blockParsers );
			}

			if ( null !== $constructor && $constructor->getNumberOfRequiredParameters() > 0 && empty( $constructorArgs ) ) {
				return null;
			}

			$instance = $reflection->newInstanceArgs( $constructorArgs );
			if ( method_exists( $instance, 'setBlockAttributes' ) ) {
				$instance->setBlockAttributes( $attrs, false );
			}

			return $instance;
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/**
	 * Build constructor args for mapped class-string resolvers.
	 *
	 * Current strategy:
	 * - if first constructor parameter accepts array, inject parsed children
	 *
	 * @param \ReflectionMethod $constructor Constructor reflection.
	 * @param array             $parsedBlock Parsed block payload.
	 * @param array             $blockParsers Local parser mapping.
	 * @return array<int, mixed>
	 */
	private static function buildMappedConstructorArgs( \ReflectionMethod $constructor, array $parsedBlock, array $blockParsers = [] ): array {
		$params = $constructor->getParameters();
		if ( empty( $params ) ) {
			return [];
		}

		$firstParam = $params[0];
		if ( ! self::parameterAcceptsArray( $firstParam ) ) {
			return [];
		}

		return [ self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers ) ];
	}

	/**
	 * Check whether a parameter accepts array values.
	 *
	 * @param \ReflectionParameter $parameter Parameter reflection.
	 * @return bool
	 */
	private static function parameterAcceptsArray( \ReflectionParameter $parameter ): bool {
		$type = $parameter->getType();

		if ( $type instanceof \ReflectionNamedType ) {
			return 'array' === $type->getName();
		}

		if ( ! $type instanceof \ReflectionUnionType ) {
			return false;
		}

		foreach ( $type->getTypes() as $namedType ) {
			if ( $namedType instanceof \ReflectionNamedType && 'array' === $namedType->getName() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build simple markup fallback for unsupported blocks.
	 *
	 * @param string $blockName Block name.
	 * @param array  $attrs Block attributes.
	 * @param array  $parsedBlock Full parsed block payload.
	 * @return string
	 */
	private static function createSimpleMarkupBlock( string $blockName, array $attrs, array $parsedBlock, array $blockParsers = [] ): string {
		$content = self::buildStringContentFromParsedBlock( $parsedBlock, $blockParsers );
		$comment = new BlockComments( $blockName, $attrs );

		if ( '' === trim( $content ) ) {
			return $comment->selfClosingComment();
		}

		return $comment->wrapContent( $content );
	}

	/**
	 * Rebuild block inner content as string from `innerContent` and children.
	 *
	 * @param array $parsedBlock Parsed block.
	 * @return string
	 */
	private static function buildStringContentFromParsedBlock( array $parsedBlock, array $blockParsers = [] ): string {
		$innerContent = is_array( $parsedBlock['innerContent'] ?? null ) ? $parsedBlock['innerContent'] : [];
		$innerBlocks  = is_array( $parsedBlock['innerBlocks'] ?? null ) ? $parsedBlock['innerBlocks'] : [];

		if ( empty( $innerContent ) ) {
			return (string) ( $parsedBlock['innerHTML'] ?? '' );
		}

		$result     = '';
		$childIndex = 0;

		foreach ( $innerContent as $chunk ) {
			if ( null === $chunk ) {
				$child = $innerBlocks[ $childIndex ] ?? null;
				if ( is_array( $child ) ) {
					$parsedChild = self::createFromParsedBlock( $child, $blockParsers );
					$result     .= self::renderToString( $parsedChild );
				}
				++$childIndex;
				continue;
			}

			$result .= (string) $chunk;
		}

		return $result;
	}

	/**
	 * Build parsed children as block objects/strings.
	 *
	 * @param array $parsedBlock Parsed block.
	 * @return array<int, object|string>
	 */
	private static function createChildrenFromInnerBlocks( array $parsedBlock, array $blockParsers = [] ): array {
		$children    = [];
		$innerBlocks = is_array( $parsedBlock['innerBlocks'] ?? null ) ? $parsedBlock['innerBlocks'] : [];

		foreach ( $innerBlocks as $innerBlock ) {
			if ( ! is_array( $innerBlock ) ) {
				continue;
			}

			$child = self::createFromParsedBlock( $innerBlock, $blockParsers );
			if ( null !== $child && '' !== $child ) {
				$children[] = $child;
			}
		}

		return $children;
	}

	/**
	 * Validate that all children are instances of allowed classes.
	 *
	 * @param array<int, mixed> $children Children values.
	 * @param array<int, string> $allowedClasses Allowed class names.
	 * @return bool
	 */
	private static function childrenMatchAllowedTypes( array $children, array $allowedClasses ): bool {
		foreach ( $children as $child ) {
			$isAllowed = false;
			foreach ( $allowedClasses as $allowedClass ) {
				if ( $child instanceof $allowedClass ) {
					$isAllowed = true;
					break;
				}
			}

			if ( ! $isAllowed ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Convert a parsed item to string.
	 *
	 * @param mixed $value Value to convert.
	 * @return string
	 */
	private static function renderToString( mixed $value ): string {
		if ( is_string( $value ) ) {
			return $value;
		}

		if ( is_object( $value ) && \method_exists( $value, 'render' ) ) {
			return (string) $value->render();
		}

		return '';
	}

	/**
	 * Create ParagraphBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return ParagraphBlock
	 */
	private static function createParagraphBlock( array $attrs, array $parsedBlock ): ParagraphBlock {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$content   = self::extractTagInnerHtml( $innerHtml, 'p' ) ?? $innerHtml;

		$block = new ParagraphBlock( $content );
		$block->setBlockAttributes( $attrs, false );

		return $block;
	}

	/**
	 * Create HeadingBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return HeadingBlock
	 */
	private static function createHeadingBlock( array $attrs, array $parsedBlock ): HeadingBlock {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$level     = (int) ( $attrs['level'] ?? self::extractHeadingLevel( $innerHtml ) ?? 2 );
		$content   = self::extractTagInnerHtml( $innerHtml, 'h' . $level ) ?? $innerHtml;

		$block = new HeadingBlock( $content, $level );
		$block->setBlockAttributes( $attrs, false );

		return $block;
	}

	/**
	 * Create GroupBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return GroupBlock
	 */
	private static function createGroupBlock( array $attrs, array $parsedBlock, array $blockParsers = [] ): GroupBlock {
		$block = new GroupBlock( self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers ) );
		$block->setBlockAttributes( $attrs, false );

		$layout = is_array( $attrs['layout'] ?? null ) ? $attrs['layout'] : [];
		$type   = $layout['type'] ?? null;

		if ( 'flex' === $type ) {
			$isVertical = 'vertical' === ( $layout['orientation'] ?? null );
			$wrap       = null;

			if ( isset( $layout['flexWrap'] ) ) {
				$wrap = 'wrap' === $layout['flexWrap'];
			}

			if ( $isVertical ) {
				$block->asFlexColumn( $wrap );
			} else {
				$block->asFlexRow( $wrap );
			}
		} elseif ( 'constrained' === $type ) {
			$block->layoutConstrained();

			if ( isset( $layout['contentSize'] ) ) {
				$block->contentSize( (string) $layout['contentSize'] );
			}

			if ( isset( $layout['wideSize'] ) ) {
				$block->wideSize( (string) $layout['wideSize'] );
			}
		} elseif ( 'grid' === $type ) {
			$block->asGrid();

			if ( isset( $layout['columnCount'] ) ) {
				$block->columnCount( (int) $layout['columnCount'] );
			}

			if ( isset( $layout['minimumColumnWidth'] ) ) {
				$block->minimumColumnWidth( (string) $layout['minimumColumnWidth'] );
			}
		} elseif ( 'flow' === $type ) {
			$block->asBlock();
		}

		if ( isset( $layout['justifyContent'] ) ) {
			$block->justifyContent( (string) $layout['justifyContent'] );
		}

		return $block;
	}

	/**
	 * Create ColumnsBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return ColumnsBlock
	 */
	private static function createColumnsBlock( array $attrs, array $parsedBlock, array $blockParsers = [] ): ?ColumnsBlock {
		$children = self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers );
		if ( ! self::childrenMatchAllowedTypes( $children, array( ColumnBlock::class ) ) ) {
			return null;
		}

		$block = new ColumnsBlock( $children );
		$block->setBlockAttributes( $attrs, false );

		if ( array_key_exists( 'isStackedOnMobile', $attrs ) && false === $attrs['isStackedOnMobile'] ) {
			$block->notStackedOnMobile();
		}

		if ( isset( $attrs['align'] ) ) {
			$block->align( (string) $attrs['align'] );
		}

		return $block;
	}

	/**
	 * Create ColumnBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return ColumnBlock
	 */
	private static function createColumnBlock( array $attrs, array $parsedBlock, array $blockParsers = [] ): ColumnBlock {
		$block = new ColumnBlock( self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers ) );
		$block->setBlockAttributes( $attrs, false );

		if ( isset( $attrs['width'] ) ) {
			$block->width( (string) $attrs['width'] );
		}

		if ( isset( $attrs['layout'] ) && is_array( $attrs['layout'] ) ) {
			$block->layout( $attrs['layout'] );
		}

		return $block;
	}

	/**
	 * Create QuoteBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return QuoteBlock
	 */
	private static function createQuoteBlock( array $attrs, array $parsedBlock, array $blockParsers = [] ): QuoteBlock {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$citation  = self::extractTagInnerHtml( $innerHtml, 'cite' );

		$block = new QuoteBlock( self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers ), $citation );
		$block->setBlockAttributes( $attrs, false );

		if ( isset( $attrs['textAlign'] ) ) {
			$block->textAlign( (string) $attrs['textAlign'] );
		}

		return $block;
	}

	/**
	 * Create PullquoteBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return PullquoteBlock
	 */
	private static function createPullquoteBlock( array $attrs, array $parsedBlock ): PullquoteBlock {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$value     = self::extractTagInnerHtml( $innerHtml, 'p' ) ?? '';
		$citation  = self::extractTagInnerHtml( $innerHtml, 'cite' );

		$block = new PullquoteBlock( $value, $citation );
		$block->setBlockAttributes( $attrs, false );

		if ( isset( $attrs['textAlign'] ) ) {
			$block->textAlign( (string) $attrs['textAlign'] );
		}

		return $block;
	}

	/**
	 * Create ListBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return ListBlock
	 */
	private static function createListBlock( array $attrs, array $parsedBlock, array $blockParsers = [] ): ?ListBlock {
		$items = self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers );
		if ( ! self::childrenMatchAllowedTypes( $items, array( ListItemBlock::class, ListBlock::class ) ) ) {
			return null;
		}

		if ( empty( $items ) && '' !== trim( (string) ( $parsedBlock['innerHTML'] ?? '' ) ) ) {
			return null;
		}

		$block = new ListBlock(
			items: $items,
			ordered: (bool) ( $attrs['ordered'] ?? false ),
			type: (string) ( $attrs['type'] ?? 'decimal' ),
			start: isset( $attrs['start'] ) ? (string) $attrs['start'] : '',
			reversed: (bool) ( $attrs['reversed'] ?? false )
		);

		$block->setBlockAttributes( $attrs, false );

		return $block;
	}

	/**
	 * Create ListItemBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return ListItemBlock
	 */
	private static function createListItemBlock( array $attrs, array $parsedBlock ): ListItemBlock {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$content   = self::extractTagInnerHtml( $innerHtml, 'li' ) ?? $innerHtml;
		$block     = new ListItemBlock( $content );

		$block->setBlockAttributes( $attrs, false );

		return $block;
	}

	/**
	 * Create SeparatorBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @return SeparatorBlock
	 */
	private static function createSeparatorBlock( array $attrs ): SeparatorBlock {
		$block = new SeparatorBlock();
		$block->setBlockAttributes( $attrs, false );

		return $block;
	}

	/**
	 * Create FileBlock.
	 *
	 * Falls back to simple markup when no attachment id exists.
	 *
	 * @param array $attrs Parsed attributes.
	 * @return FileBlock|null
	 */
	private static function createFileBlock( array $attrs ): ?FileBlock {
		if ( ! isset( $attrs['id'] ) ) {
			return null;
		}

		$id = (int) $attrs['id'];
		if ( $id <= 0 ) {
			return null;
		}

		$block = new FileBlock(
			attachmentId: $id,
			showDownloadButton: (bool) ( $attrs['showDownloadButton'] ?? true ),
			openInNewTab: (bool) ( $attrs['openInNewTab'] ?? false ),
			downloadButtonText: (string) ( $attrs['downloadButtonText'] ?? 'Download' )
		);

		if ( isset( $attrs['href'] ) ) {
			$block->href( (string) $attrs['href'] );
		}

		$block->setBlockAttributes( $attrs, false );

		return $block;
	}

	/**
	 * Create ImageBlock.
	 *
	 * Falls back to simple markup when no attachment id exists.
	 *
	 * @param array $attrs Parsed attributes.
	 * @return ImageBlock|null
	 */
	private static function createImageBlock( array $attrs ): ?ImageBlock {
		if ( ! isset( $attrs['id'] ) ) {
			return null;
		}

		$id = (int) $attrs['id'];
		if ( $id <= 0 ) {
			return null;
		}

		$block = new ImageBlock(
			imageId: $id,
			imageSize: (string) ( $attrs['sizeSlug'] ?? 'full' ),
			linkDestination: (string) ( $attrs['linkDestination'] ?? 'none' ),
			lightbox: is_array( $attrs['lightbox'] ?? null ) ? $attrs['lightbox'] : null,
			aspectRatio: isset( $attrs['aspectRatio'] ) ? (string) $attrs['aspectRatio'] : null,
			scale: isset( $attrs['scale'] ) ? (string) $attrs['scale'] : null,
			width: isset( $attrs['width'] ) ? (string) $attrs['width'] : null,
			height: isset( $attrs['height'] ) ? (string) $attrs['height'] : null,
			href: isset( $attrs['href'] ) ? (string) $attrs['href'] : null
		);

		$block->setBlockAttributes( $attrs, false );

		return $block;
	}

	/**
	 * Extract first tag inner HTML.
	 *
	 * @param string $html Source HTML.
	 * @param string $tag Tag name.
	 * @return string|null
	 */
	private static function extractTagInnerHtml( string $html, string $tag ): ?string {
		$pattern = sprintf( '/<%1$s\\b[^>]*>(.*?)<\\/%1$s>/is', preg_quote( $tag, '/' ) );
		if ( 1 === preg_match( $pattern, $html, $matches ) ) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Extract heading level from heading HTML.
	 *
	 * @param string $html Source HTML.
	 * @return int|null
	 */
	private static function extractHeadingLevel( string $html ): ?int {
		if ( 1 === preg_match( '/<h([1-6])\\b/i', $html, $matches ) ) {
			return (int) $matches[1];
		}

		return null;
	}
}
