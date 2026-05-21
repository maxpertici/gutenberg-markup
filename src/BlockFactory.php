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
	 * Parse post content and create blocks.
	 *
	 * Supported Gutenberg blocks are converted to dedicated classes.
	 * Unsupported blocks are returned as simple markup strings.
	 *
	 * @param string $postContent Raw Gutenberg post content.
	 * @return array<int, object|string>
	 */
	public static function parsePostContent( string $postContent ): array {
		if ( ! \function_exists( 'parse_blocks' ) ) {
			return [ $postContent ];
		}

		$parsedBlocks = \parse_blocks( $postContent );
		$blocks       = [];

		foreach ( $parsedBlocks as $parsedBlock ) {
			$block = self::createFromParsedBlock( $parsedBlock );
			if ( null !== $block && '' !== $block ) {
				$blocks[] = $block;
			}
		}

		return $blocks;
	}

	/**
	 * Create a block instance (or markup fallback) from parse_blocks() output.
	 *
	 * @param array $parsedBlock A parsed block item.
	 * @return object|string|null
	 */
	private static function createFromParsedBlock( array $parsedBlock ) {
		$blockName = $parsedBlock['blockName'] ?? null;
		$attrs     = is_array( $parsedBlock['attrs'] ?? null ) ? $parsedBlock['attrs'] : [];

		if ( empty( $blockName ) ) {
			return self::buildStringContentFromParsedBlock( $parsedBlock );
		}

		$block = self::createSupportedBlock( $blockName, $attrs, $parsedBlock );

		if ( null !== $block ) {
			return $block;
		}

		return self::createSimpleMarkupBlock( $blockName, $attrs, $parsedBlock );
	}

	/**
	 * Create a dedicated block instance for supported block names.
	 *
	 * @param string $blockName Block name from parse_blocks().
	 * @param array  $attrs Parsed block attributes.
	 * @param array  $parsedBlock Full parsed block payload.
	 * @return object|null
	 */
	private static function createSupportedBlock( string $blockName, array $attrs, array $parsedBlock ): ?object {
		return match ( $blockName ) {
			'core/paragraph' => self::createParagraphBlock( $attrs, $parsedBlock ),
			'core/heading' => self::createHeadingBlock( $attrs, $parsedBlock ),
			'core/group' => self::createGroupBlock( $attrs, $parsedBlock ),
			'core/columns' => self::createColumnsBlock( $attrs, $parsedBlock ),
			'core/column' => self::createColumnBlock( $attrs, $parsedBlock ),
			'core/quote' => self::createQuoteBlock( $attrs, $parsedBlock ),
			'core/pullquote' => self::createPullquoteBlock( $attrs, $parsedBlock ),
			'core/list' => self::createListBlock( $attrs, $parsedBlock ),
			'core/list-item' => self::createListItemBlock( $attrs, $parsedBlock ),
			'core/separator' => self::createSeparatorBlock( $attrs ),
			'core/file' => self::createFileBlock( $attrs ),
			'core/image' => self::createImageBlock( $attrs ),
			default => null,
		};
	}

	/**
	 * Build simple markup fallback for unsupported blocks.
	 *
	 * @param string $blockName Block name.
	 * @param array  $attrs Block attributes.
	 * @param array  $parsedBlock Full parsed block payload.
	 * @return string
	 */
	private static function createSimpleMarkupBlock( string $blockName, array $attrs, array $parsedBlock ): string {
		$content = self::buildStringContentFromParsedBlock( $parsedBlock );
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
	private static function buildStringContentFromParsedBlock( array $parsedBlock ): string {
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
					$parsedChild = self::createFromParsedBlock( $child );
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
	private static function createChildrenFromInnerBlocks( array $parsedBlock ): array {
		$children    = [];
		$innerBlocks = is_array( $parsedBlock['innerBlocks'] ?? null ) ? $parsedBlock['innerBlocks'] : [];

		foreach ( $innerBlocks as $innerBlock ) {
			if ( ! is_array( $innerBlock ) ) {
				continue;
			}

			$child = self::createFromParsedBlock( $innerBlock );
			if ( null !== $child && '' !== $child ) {
				$children[] = $child;
			}
		}

		return $children;
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
	private static function createGroupBlock( array $attrs, array $parsedBlock ): GroupBlock {
		$block = new GroupBlock( self::createChildrenFromInnerBlocks( $parsedBlock ) );
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
	private static function createColumnsBlock( array $attrs, array $parsedBlock ): ColumnsBlock {
		$children = array_values(
			array_filter(
				self::createChildrenFromInnerBlocks( $parsedBlock ),
				fn( $child ) => $child instanceof ColumnBlock
			)
		);

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
	private static function createColumnBlock( array $attrs, array $parsedBlock ): ColumnBlock {
		$block = new ColumnBlock( self::createChildrenFromInnerBlocks( $parsedBlock ) );
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
	private static function createQuoteBlock( array $attrs, array $parsedBlock ): QuoteBlock {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$citation  = self::extractTagInnerHtml( $innerHtml, 'cite' );

		$block = new QuoteBlock( self::createChildrenFromInnerBlocks( $parsedBlock ), $citation );
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
	private static function createListBlock( array $attrs, array $parsedBlock ): ListBlock {
		$items = self::createChildrenFromInnerBlocks( $parsedBlock );
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

