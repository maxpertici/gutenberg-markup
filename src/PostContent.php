<?php
/**
 * Post content helper for Gutenberg parsed tree manipulations.
 *
 * @package MaxPertici\GutenbergMarkup
 */

namespace MaxPertici\GutenbergMarkup;

class PostContent {

	/**
	 * Parsed Gutenberg blocks tree.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $parsedBlocks = [];

	/**
	 * Default local parser mapping used when converting to block objects.
	 *
	 * @var array<string, callable|string>
	 */
	private array $blockParsers = [];

	/**
	 * @param string|array<int, array<string, mixed>> $postContent Raw Gutenberg markup or parsed blocks array (same structure as parse_blocks()).
	 * @param array<string, callable|string>          $blockParsers Local parser mapping.
	 */
	public function __construct( string|array $postContent, array $blockParsers = [] ) {
		$this->blockParsers = $blockParsers;
		$this->parsedBlocks = is_string( $postContent )
			? self::parseMarkupToBlocksTree( $postContent )
			: self::normalizeParsedBlocks( $postContent );
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
	 * @param callable(array<string,mixed>): array  $updater Updater callback receiving block array and returning updated block array.
	 *                                              Non-array return values are ignored (no update applied).
	 * @return bool True when one block was updated.
	 */
	public function updateFirst( string $blockName, callable $updater ): bool {
		return self::updateFirstRecursive( $this->parsedBlocks, $blockName, $updater );
	}

	/**
	 * Update all matching blocks in parsed tree.
	 *
	 * @param string                                $blockName Block name (e.g. core/group).
	 * @param callable(array<string,mixed>): array  $updater Updater callback receiving block array and returning updated block array.
	 *                                              Non-array return values are ignored (no update applied).
	 * @return int Number of updated blocks.
	 */
	public function updateAll( string $blockName, callable $updater ): int {
		return self::updateAllRecursive( $this->parsedBlocks, $blockName, $updater );
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
	 * @param callable(array<string,mixed>): array  $updater Updater callback.
	 *                                              Non-array return values are ignored.
	 * @return bool
	 */
	private static function updateFirstRecursive( array &$blocks, string $blockName, callable $updater ): bool {
		foreach ( $blocks as $index => &$block ) {
			$currentBlockName = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			if ( $currentBlockName === $blockName ) {
				$updated = $updater( $block );
				if ( is_array( $updated ) ) {
					$blocks[ $index ] = $updated;
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
	 * @param callable(array<string,mixed>): array  $updater Updater callback.
	 *                                              Non-array return values are ignored.
	 * @return int
	 */
	private static function updateAllRecursive( array &$blocks, string $blockName, callable $updater ): int {
		$updatedCount = 0;

		foreach ( $blocks as $index => &$block ) {
			$currentBlockName = isset( $block['blockName'] ) && is_string( $block['blockName'] ) ? $block['blockName'] : '';
			if ( $currentBlockName === $blockName ) {
				$updated = $updater( $block );
				if ( is_array( $updated ) ) {
					$blocks[ $index ] = $updated;
					$block            = $blocks[ $index ];
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

}
