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
 * Supports targeted block-level search/update operations and conversion
 * back to typed blocks or Gutenberg markup for safe large-scale transforms.
 */
class PostContent extends Markup {

	/**
	 * Original raw Gutenberg markup when constructed from string input.
	 *
	 * @var string|null
	 */
	private ?string $originalMarkup = null;

	/**
	 * Parsed Gutenberg blocks tree.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $parsedBlocks = [];

	/**
	 * Indicates whether parsed blocks were effectively updated.
	 *
	 * @var bool
	 */
	private bool $hasUpdates = false;

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
		$this->originalMarkup = is_string( $contentOrBlocks ) ? $contentOrBlocks : null;
		$this->parsedBlocks = is_string( $contentOrBlocks )
			? self::parseMarkupToBlocksTree( $contentOrBlocks )
			: self::normalizeParsedBlocks( $contentOrBlocks );

		parent::__construct(
			'',
			[],
			[],
			'',
			self::createMarkupChildrenFromParsedBlocks( $this->parsedBlocks )
		);
	}

	/**
	 * Return parsed Gutenberg blocks tree.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function parsedBlocks(): array {
		return $this->parsedBlocks;
	}

	/**
	 * Find first block by block name.
	 *
	 * @param string $blockName Block name (e.g. core/group).
	 * @return array<string, mixed>|null
	 */
	public function findFirst( string $blockName ): ?array {
		return self::findFirstRecursive( $this->parsedBlocks, $blockName );
	}

	/**
	 * Find all blocks by block name.
	 *
	 * @param string $blockName Block name (e.g. core/group).
	 * @return array<int, array<string, mixed>>
	 */
	public function findAll( string $blockName ): array {
		$found = [];
		self::findAllRecursive( $this->parsedBlocks, $blockName, $found );

		return $found;
	}

	/**
	 * Update first matching block in parsed tree.
	 *
	 * @param string                                $blockName Block name (e.g. core/group).
	 * @param callable(array<string, mixed>): array $updater Updater callback receiving block array and returning updated block array.
	 *                                              Non-array return values are ignored (no update applied).
	 * @return bool True when one block was updated.
	 */
	public function updateFirst( string $blockName, callable $updater ): bool {
		$updated = self::updateFirstRecursive( $this->parsedBlocks, $blockName, $updater );
		if ( $updated ) {
			$this->hasUpdates = true;
			$this->syncMarkupTree();
		}

		return $updated;
	}

	/**
	 * Update all matching blocks in parsed tree.
	 *
	 * @param string                                $blockName Block name (e.g. core/group).
	 * @param callable(array<string, mixed>): array $updater Updater callback receiving block array and returning updated block array.
	 *                                              Non-array return values are ignored (no update applied).
	 * @return int Number of updated blocks.
	 */
	public function updateAll( string $blockName, callable $updater ): int {
		$updatedCount = self::updateAllRecursive( $this->parsedBlocks, $blockName, $updater );
		if ( $updatedCount > 0 ) {
			$this->hasUpdates = true;
			$this->syncMarkupTree();
		}

		return $updatedCount;
	}

	/**
	 * Convert parsed tree to nested BlockMarkup/string structure.
	 *
	 * @return array<int, BlockMarkup|string>
	 */
	public function toBlockMarkup(): array {
		return self::createMarkupChildrenFromParsedBlocks( $this->parsedBlocks );
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
	 * Convert parsed tree to typed block objects / markup fallback strings.
	 *
	 * @param array<string, callable|string>|null $blockParsers Local parser mapping override.
	 * @return array<int, object|string>
	 */
	public function toBlocks( ?array $blockParsers = null ): array {
		$resolvers = null === $blockParsers ? $this->blockParsers : $blockParsers;

		return BlockFactory::parseParsedBlocks( $this->parsedBlocks, $resolvers );
	}

	/**
	 * Convert current parsed tree back to Gutenberg markup.
	 *
	 * @param array<string, callable|string>|null $blockParsers Local parser mapping override used by fallback renderer.
	 * @return string
	 */
	public function toMarkup( ?array $blockParsers = null ): string {
		if ( $this->shouldReturnOriginalMarkup( $blockParsers ) ) {
			return $this->originalMarkup;
		}

		if ( \function_exists( 'serialize_blocks' ) ) {
			// @phpstan-ignore-next-line
			return (string) \serialize_blocks( $this->parsedBlocks );
		}

		$output = '';
		foreach ( $this->toBlocks( $blockParsers ) as $block ) {
			$output .= self::renderValueToString( $block );
		}

		return $output;
	}

	/**
	 * Determine if original raw markup should be returned as-is.
	 *
	 * @param array<string, callable|string>|null $blockParsers Local parser mapping override.
	 * @return bool
	 */
	private function shouldReturnOriginalMarkup( ?array $blockParsers ): bool {
		return null === $blockParsers && ! $this->hasUpdates && null !== $this->originalMarkup;
	}

	/**
	 * @param string $postContent Raw Gutenberg markup.
	 * @return array<int, array<string, mixed>>
	 */
	private static function parseMarkupToBlocksTree( string $postContent ): array {
		if ( \function_exists( 'parse_blocks' ) ) {
			// @phpstan-ignore-next-line
			return self::normalizeParsedBlocks( \parse_blocks( $postContent ) );
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
	 * @param array<int, array<string, mixed>> $blocks Parsed blocks.
	 * @param string                            $blockName Block name.
	 * @return array<string, mixed>|null
	 */
	private static function findFirstRecursive( array $blocks, string $blockName ): ?array {
		foreach ( $blocks as $block ) {
			$currentBlockName = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			if ( $currentBlockName === $blockName ) {
				return $block;
			}

			$innerBlocks = is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [];
			$found       = self::findFirstRecursive( self::normalizeParsedBlocks( $innerBlocks ), $blockName );
			if ( null !== $found ) {
				return $found;
			}
		}

		return null;
	}

	/**
	 * @param array<int, array<string, mixed>>       $blocks Parsed blocks.
	 * @param string                                 $blockName Block name.
	 * @param array<int, array<string, mixed>>       $found Collector.
	 * @return void
	 */
	private static function findAllRecursive( array $blocks, string $blockName, array &$found ): void {
		foreach ( $blocks as $block ) {
			$currentBlockName = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			if ( $currentBlockName === $blockName ) {
				$found[] = $block;
			}

			$innerBlocks = is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [];
			self::findAllRecursive( self::normalizeParsedBlocks( $innerBlocks ), $blockName, $found );
		}
	}

	/**
	 * @param array<int, array<string, mixed>>      $blocks Parsed blocks by reference.
	 * @param string                                $blockName Block name.
	 * @param callable(array<string, mixed>): array $updater Updater callback.
	 *                                              Non-array return values are ignored.
	 * @return bool
	 */
	private static function updateFirstRecursive( array &$blocks, string $blockName, callable $updater ): bool {
		foreach ( $blocks as &$block ) {
			$currentBlockName = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			if ( $currentBlockName === $blockName ) {
				$updated = $updater( $block );
				if ( is_array( $updated ) ) {
					$block = $updated;
					return true;
				}
			}

			$innerBlocks = is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [];
			if ( empty( $innerBlocks ) ) {
				continue;
			}

			if ( self::updateFirstRecursive( $innerBlocks, $blockName, $updater ) ) {
				$block['innerBlocks'] = $innerBlocks;
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<int, array<string, mixed>>      $blocks Parsed blocks by reference.
	 * @param string                                $blockName Block name.
	 * @param callable(array<string, mixed>): array $updater Updater callback.
	 *                                              Non-array return values are ignored.
	 * @return int
	 */
	private static function updateAllRecursive( array &$blocks, string $blockName, callable $updater ): int {
		$updatedCount = 0;

		foreach ( $blocks as &$block ) {
			$currentBlockName = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			if ( $currentBlockName === $blockName ) {
				$updated = $updater( $block );
				if ( is_array( $updated ) ) {
					$block = $updated;
					++$updatedCount;
				}
			}

			$innerBlocks = is_array( $block['innerBlocks'] ?? null ) ? $block['innerBlocks'] : [];
			if ( ! empty( $innerBlocks ) ) {
				$updatedCount          += self::updateAllRecursive( $innerBlocks, $blockName, $updater );
				$block['innerBlocks'] = $innerBlocks;
			}
		}

		return $updatedCount;
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
	 * Synchronize Markup children with the current parsed blocks tree.
	 *
	 * @return void
	 */
	private function syncMarkupTree(): void {
		$this->setChildren( self::createMarkupChildrenFromParsedBlocks( $this->parsedBlocks ) );
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
