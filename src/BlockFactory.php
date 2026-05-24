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
use MaxPertici\GutenbergMarkup\Blocks\ButtonBlock;
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

/**
 * Factory responsible for parsing Gutenberg content and building block instances.
 *
 * It supports custom/local parser mappings, known native block conversions,
 * automatic class resolution by naming convention, and safe markup fallback.
 */
class BlockFactory {

	/**
	 * Known native supported Gutenberg block names.
	 *
	 * @var array<int, string>
	 */
	private const KNOWN_NATIVE_SUPPORTED_BLOCKS = array(
		'core/paragraph',
		'core/heading',
		'core/button',
		'core/group',
		'core/columns',
		'core/column',
		'core/quote',
		'core/pullquote',
		'core/list',
		'core/list-item',
		'core/separator',
		'core/file',
		'core/image',
	);

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
	 * Native supported blocks enabled for conversion.
	 *
	 * Empty by default for safe fallback-only behavior.
	 *
	 * @var array<int, string>
	 */
	private static array $nativeSupportedBlocks = [];

	/**
	 * Automatic class-resolution by naming convention toggle.
	 *
	 * Disabled by default for safe fallback-only behavior.
	 *
	 * @var bool
	 */
	private static bool $automaticClassResolutionEnabled = false;

	/**
	 * Optional debug logger callback.
	 *
	 * Signature: fn(string $event, array $context): void
	 *
	 * @var callable|null
	 */
	private static $debugLogger = null;

	/**
	 * Expose runtime parser availability.
	 *
	 * @return bool
	 */
	public static function isNativeParserAvailable(): bool {
		return \function_exists( 'parse_blocks' );
	}

	/**
	 * Register a debug logger callback.
	 *
	 * @param callable|null $logger fn(string $event, array $context): void
	 * @return void
	 */
	public static function setDebugLogger( ?callable $logger ): void {
		self::$debugLogger = $logger;
	}

	/**
	 * Remove debug logger callback.
	 *
	 * @return void
	 */
	public static function clearDebugLogger(): void {
		self::$debugLogger = null;
	}

	/**
	 * Parse post content and create blocks.
	 *
	 * Supported Gutenberg blocks are converted to dedicated classes.
	 * Unsupported blocks are returned as simple markup strings.
	 *
	 * @param string $postContent Raw Gutenberg post content.
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
	 * @param bool $prepareForFind Optional. Auto-run prepareForFind(true) on parsed block objects. Default true.
	 * @return array<int, object|string>
	 */
	public static function parsePostContent( string $postContent, array $blockParsers = [], bool $prepareForFind = true ): array {
		if ( ! self::isNativeParserAvailable() ) {
			self::debug( 'native_parser_unavailable', array() );
			return [ $postContent ];
		}

		$parser       = 'parse_blocks';
		$parsedBlocks = $parser( $postContent );

		return self::parseParsedBlocks( is_array( $parsedBlocks ) ? $parsedBlocks : array(), $blockParsers, $prepareForFind );
	}

	/**
	 * Create block instances from a parsed Gutenberg blocks tree.
	 *
	 * @param array<int, array<string, mixed>> $parsedBlocks Parsed blocks from parse_blocks().
	 * @param array<string, callable|string>   $blockParsers Local parser mapping.
	 * @param bool                              $prepareForFind Auto-run prepareForFind(true) on parsed block objects.
	 * @return array<int, object|string>
	 */
	public static function parseParsedBlocks( array $parsedBlocks, array $blockParsers = [], bool $prepareForFind = false ): array {
		$blocks = [];

		foreach ( $parsedBlocks as $parsedBlock ) {
			if ( ! is_array( $parsedBlock ) ) {
				continue;
			}

			$attrs = is_array( $parsedBlock['attrs'] ?? null ) ? $parsedBlock['attrs'] : [];

			$block = self::createFromParsedBlock( $parsedBlock, $blockParsers );
			if ( null !== $block && '' !== $block ) {
				$blocks[] = self::finalizeParsedBlockObjectState( $block, $attrs, $prepareForFind );
			}
		}

		return $blocks;
	}

	/**
	 * Normalize parsed block object state for finder/runtime usage.
	 *
	 * - Hydrates from attrs when supported by the object
	 * - Optionally prepares the full tree for finder queries
	 *
	 * @param object|string $block Parsed block result.
	 * @param array<string, mixed> $attrs Parsed Gutenberg attrs.
	 * @param bool $prepareForFind Whether to call prepareForFind(true).
	 * @return object|string
	 */
	private static function finalizeParsedBlockObjectState( object|string $block, array $attrs, bool $prepareForFind ): object|string {
		if ( ! is_object( $block ) ) {
			return $block;
		}

		if ( method_exists( $block, 'hydrate' ) ) {
			$block->hydrate( $attrs, false );
		}

		if ( $prepareForFind && method_exists( $block, 'prepareForFind' ) ) {
			$block->prepareForFind( true );
		}

		return $block;
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
	 * Enable one native supported block conversion.
	 *
	 * @param string $blockName Gutenberg block name.
	 * @return void
	 */
	public static function registerNativeSupportedBlock( string $blockName ): void {
		if ( ! in_array( $blockName, self::KNOWN_NATIVE_SUPPORTED_BLOCKS, true ) ) {
			self::debug(
				'unknown_native_supported_block',
				array( 'blockName' => $blockName )
			);
			return;
		}

		if ( ! in_array( $blockName, self::$nativeSupportedBlocks, true ) ) {
			self::$nativeSupportedBlocks[] = $blockName;
		}
	}

	/**
	 * Enable multiple native supported block conversions.
	 *
	 * @param array<int, string> $blockNames Gutenberg block names.
	 * @return void
	 */
	public static function registerNativeSupportedBlocks( array $blockNames ): void {
		foreach ( $blockNames as $blockName ) {
			if ( is_string( $blockName ) ) {
				self::registerNativeSupportedBlock( $blockName );
			}
		}
	}

	/**
	 * Clear enabled native supported block conversions.
	 *
	 * @return void
	 */
	public static function clearNativeSupportedBlocks(): void {
		self::$nativeSupportedBlocks = [];
	}

	/**
	 * Get enabled native supported block names.
	 *
	 * @return array<int, string>
	 */
	public static function nativeSupportedBlocks(): array {
		return self::$nativeSupportedBlocks;
	}

	/**
	 * Enable/disable automatic class resolution by naming convention.
	 *
	 * @param bool $enabled True to enable; false to disable.
	 * @return void
	 */
	public static function enableAutomaticClassResolution( bool $enabled = true ): void {
		self::$automaticClassResolutionEnabled = $enabled;
	}

	/**
	 * Whether automatic class resolution is enabled.
	 *
	 * @return bool
	 */
	public static function isAutomaticClassResolutionEnabled(): bool {
		return self::$automaticClassResolutionEnabled;
	}

	/**
	 * Create a block instance (or markup fallback) from parse_blocks() output.
	 *
	 * @param array<string, mixed> $parsedBlock A parsed block item.
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
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

		if ( self::$automaticClassResolutionEnabled ) {
			$block = self::createAutomaticBlock( $blockName, $attrs, $parsedBlock );
			if ( null !== $block ) {
				return $block;
			}
		}

		return self::createSimpleMarkupBlock( $blockName, $attrs, $parsedBlock, $blockParsers );
	}

	/**
	 * Create a dedicated block instance for supported block names.
	 *
	 * @param string $blockName Block name from parse_blocks().
	 * @param array  $attrs Parsed block attributes.
	 * @param array  $parsedBlock Full parsed block payload.
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
	 * @return object|null
	 */
	private static function createSupportedBlock( string $blockName, array $attrs, array $parsedBlock, array $blockParsers = [] ): ?object {
		if ( ! in_array( $blockName, self::$nativeSupportedBlocks, true ) ) {
			return null;
		}

		return match ( $blockName ) {
			'core/paragraph' => self::createParagraphBlock( $attrs, $parsedBlock ),
			'core/heading' => self::createHeadingBlock( $attrs, $parsedBlock ),
			'core/button' => self::createButtonBlock( $attrs, $parsedBlock ),
			'core/group' => self::createGroupBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/columns' => self::createColumnsBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/column' => self::createColumnBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/quote' => self::createQuoteBlock( $attrs, $parsedBlock, $blockParsers ),
			'core/pullquote' => self::createPullquoteBlock( $attrs, $parsedBlock, $blockParsers ),
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
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
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
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
	 * @param bool   $hydrateConstructorFromParsedBlock Whether to hydrate constructor args from parsed block.
	 * @return object|null
	 */
	private static function createBlockFromClass( string $className, array $parsedBlock, array $attrs, array $blockParsers = [], bool $hydrateConstructorFromParsedBlock = false ): ?object {
		if ( ! class_exists( $className ) ) {
			self::debug(
				'class_not_found',
				array(
					'className' => $className,
					'blockName' => is_string( $parsedBlock['blockName'] ?? null ) ? $parsedBlock['blockName'] : null,
				)
			);
			return null;
		}

		try {
			if ( method_exists( $className, 'fromParsedBlock' ) ) {
				$instance = $className::fromParsedBlock( $parsedBlock );
				if ( is_object( $instance ) ) {
					return $instance;
				}

				self::debug(
					'from_parsed_block_invalid_return',
					array(
						'className' => $className,
						'blockName' => is_string( $parsedBlock['blockName'] ?? null ) ? $parsedBlock['blockName'] : null,
					)
				);
			}

			$reflection  = new \ReflectionClass( $className );
			$constructor = $reflection->getConstructor();
			$constructorArgs = [];
			if ( null !== $constructor && $hydrateConstructorFromParsedBlock ) {
				$constructorArgs = self::buildMappedConstructorArgs( $constructor, $parsedBlock, $blockParsers );
			}

			if ( null !== $constructor && $constructor->getNumberOfRequiredParameters() > 0 && empty( $constructorArgs ) ) {
				self::debug(
					'unresolvable_constructor_requirements',
					array(
						'className' => $className,
						'requiredParameters' => $constructor->getNumberOfRequiredParameters(),
						'hydrateConstructorFromParsedBlock' => $hydrateConstructorFromParsedBlock,
						'blockName' => is_string( $parsedBlock['blockName'] ?? null ) ? $parsedBlock['blockName'] : null,
					)
				);
				return null;
			}

			$instance = $reflection->newInstanceArgs( $constructorArgs );
			if ( method_exists( $instance, 'setBlockAttributes' ) ) {
				$instance->setBlockAttributes( $attrs, false );
			}

			return $instance;
		} catch ( \Throwable $e ) {
			self::debug(
				'class_instantiation_failed',
				array(
					'className' => $className,
					'blockName' => is_string( $parsedBlock['blockName'] ?? null ) ? $parsedBlock['blockName'] : null,
					'errorClass' => get_class( $e ),
					'errorMessage' => $e->getMessage(),
				)
			);
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
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
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
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
	 * @return PostContentBlock
	 */
	private static function createSimpleMarkupBlock( string $blockName, array $attrs, array $parsedBlock, array $blockParsers = [] ): PostContentBlock {
		$innerContent = is_array( $parsedBlock['innerContent'] ?? null ) ? $parsedBlock['innerContent'] : [];
		$children     = self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers );

		return new PostContentBlock( $blockName, $attrs, $innerContent, $children );
	}

	/**
	 * Validate minimal parsed-block shape required by serialize_block().
	 *
	 * @param array<string, mixed> $parsedBlock Parsed block payload.
	 * @return bool
	 */
	private static function hasSerializableParsedBlockShape( array $parsedBlock ): bool {
		return array_key_exists( 'blockName', $parsedBlock )
			&& array_key_exists( 'attrs', $parsedBlock )
			&& array_key_exists( 'innerBlocks', $parsedBlock )
			&& array_key_exists( 'innerHTML', $parsedBlock )
			&& array_key_exists( 'innerContent', $parsedBlock );
	}

	/**
	 * Rebuild block inner content as string from `innerContent` and children.
	 *
	 * @param array<string, mixed> $parsedBlock Parsed block.
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
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
	 * @param array<string, mixed> $parsedBlock Parsed block.
	 * @param array<string, callable|string> $blockParsers Local parser mapping.
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
	 * Emit one debug event if a logger is registered.
	 *
	 * @param string $event Event identifier.
	 * @param array<string, mixed> $context Event context.
	 * @return void
	 */
	private static function debug( string $event, array $context = array() ): void {
		if ( ! is_callable( self::$debugLogger ) ) {
			return;
		}

		try {
			$logger = self::$debugLogger;
			$logger( $event, $context );
		} catch ( \Throwable $e ) {
			// Never break parsing because of debug logger errors.
		}
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
	 * Create ButtonBlock.
	 *
	 * @param array $attrs Parsed attributes.
	 * @param array $parsedBlock Parsed block.
	 * @return ButtonBlock
	 */
	private static function createButtonBlock( array $attrs, array $parsedBlock ): ButtonBlock {
		$innerHtml   = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$content     = self::extractButtonContent( $parsedBlock, $attrs );
		$url         = (string) ( $attrs['url'] ?? self::extractTagAttribute( $innerHtml, 'a', 'href' ) ?? '' );
		$linkTarget  = isset( $attrs['linkTarget'] ) ? (string) $attrs['linkTarget'] : self::extractTagAttribute( $innerHtml, 'a', 'target' );
		$rel         = isset( $attrs['rel'] ) ? (string) $attrs['rel'] : self::extractTagAttribute( $innerHtml, 'a', 'rel' );
		$buttonBlock = new ButtonBlock( $content, $url );

		if ( null !== $linkTarget && '' !== $linkTarget ) {
			$buttonBlock->linkTarget( $linkTarget );
		}

		if ( null !== $rel && '' !== $rel ) {
			$buttonBlock->rel( $rel );
		}

		$buttonBlock->setBlockAttributes( $attrs, false );

		return $buttonBlock;
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
	 * @return ColumnsBlock|null
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
	private static function createPullquoteBlock( array $attrs, array $parsedBlock, array $blockParsers = [] ): PullquoteBlock {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$children  = self::createChildrenFromInnerBlocks( $parsedBlock, $blockParsers );
		$value     = self::extractTagInnerHtml( $innerHtml, 'p' ) ?? '';
		$citation  = self::extractTagInnerHtml( $innerHtml, 'cite' );

		$valueOrChildren = empty( $children ) ? $value : $children;
		$block           = new PullquoteBlock( $valueOrChildren, $citation );
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
	 * @return ListBlock|null
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
	 * Extract one attribute value from the first matching HTML tag.
	 *
	 * @param string $html Source HTML.
	 * @param string $tag Tag name.
	 * @param string $attribute Attribute name.
	 * @return string|null
	 */
	private static function extractTagAttribute( string $html, string $tag, string $attribute ): ?string {
		$pattern = sprintf(
			'/<%1$s\\b[^>]*\\b%2$s=(["\'])(.*?)\\1/is',
			preg_quote( $tag, '/' ),
			preg_quote( $attribute, '/' )
		);

		if ( 1 === preg_match( $pattern, $html, $matches ) ) {
			return $matches[2];
		}

		return null;
	}

	/**
	 * Extract plain-text button content from parsed block payload.
	 *
	 * Precedence order:
	 * - anchor inner HTML
	 * - `text` attribute
	 * - full inner HTML
	 *
	 * @param array<string, mixed> $parsedBlock Parsed block payload.
	 * @param array<string, mixed> $attrs Parsed block attributes.
	 * @return string
	 */
	private static function extractButtonContent( array $parsedBlock, array $attrs ): string {
		$innerHtml = (string) ( $parsedBlock['innerHTML'] ?? '' );
		$content   = self::extractTagInnerHtml( $innerHtml, 'a' )
			?? ( isset( $attrs['text'] ) ? (string) $attrs['text'] : null )
			?? $innerHtml;

		return trim( strip_tags( html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
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
