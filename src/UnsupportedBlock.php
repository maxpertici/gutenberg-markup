<?php
/**
 * Unsupported Gutenberg block fallback object.
 *
 * @package MaxPertici\GutenbergMarkup
 */

namespace MaxPertici\GutenbergMarkup;

/**
 * Fallback block used when a parsed block has no dedicated class.
 *
 * Extends BlockMarkup so it is a first-class citizen of the Markup tree:
 * all MarkupFinder methods (find(), findByClass(), findByTag(), findFirst()…)
 * work transparently on it and traverse its children.
 *
 * render() is overridden to faithfully reassemble the original markup by
 * interleaving raw innerContent HTML chunks with rendered children objects,
 * exactly as serialize_block() would — without touching $wrapper or $childrenWrapper.
 */
class UnsupportedBlock extends BlockMarkup {

	/**
	 * innerContent array from parse_blocks(): string chunks interleaved with null
	 * placeholders indicating child-block positions.
	 *
	 * @var array<int, string|null>
	 */
	private array $innerContent;

	/**
	 * @param string                    $blockName       Gutenberg block name.
	 * @param array<string, mixed>      $blockAttributes Block attributes.
	 * @param array<int, string|null>   $innerContent    Raw innerContent chunks from parse_blocks().
	 * @param array<int, object|string> $children        Parsed child block objects (BlockMarkup instances or strings).
	 */
	public function __construct(
		string $blockName,
		array $blockAttributes = [],
		array $innerContent = [],
		array $children = []
	) {
		parent::__construct(
			blockName: $blockName,
			blockAttributes: $blockAttributes,
			isSelfClosing: empty( $innerContent ) && empty( $children ),
			children: $children,
		);

		$this->innerContent = $innerContent;
	}

	/**
	 * Build unsupported block runtime state before rendering/search.
	 *
	 * @return void
	 */
	protected function build(): void {
		$this->isSelfClosing = empty( $this->innerContent ) && empty( $this->getChildren() );
	}

	/**
	 * Add one child block/content to this unsupported block.
	 *
	 * @param object|string $child Child block or raw string content.
	 * @return self
	 */
	public function addChild( object|string $child ): self {
		$this->children[] = $child;
		$this->ensureInnerContentSlotsForChildren();
		$this->build();

		return $this;
	}

	/**
	 * Add multiple child blocks/content to this unsupported block.
	 *
	 * @param array<int, object|string> $children Children to append.
	 * @return self
	 */
	public function addChildren( array $children ): self {
		foreach ( $children as $child ) {
			if ( ! is_string( $child ) && ! is_object( $child ) ) {
				continue;
			}

			$this->children[] = $child;
		}

		$this->ensureInnerContentSlotsForChildren();
		$this->build();

		return $this;
	}

	/**
	 * Replace all children for this unsupported block.
	 *
	 * @param array<int, object|string> $children Child block/content list.
	 * @return self
	 */
	public function setChildren( array $children ): self {
		$this->children = array_values(
			array_filter(
				$children,
				static fn ( mixed $child ): bool => is_string( $child ) || is_object( $child )
			)
		);

		$this->ensureInnerContentSlotsForChildren();
		$this->build();

		return $this;
	}

	/**
	 * Reassemble the block markup by interleaving HTML chunks and rendered children.
	 *
	 * Bypasses BlockMarkup's $wrapper/$childrenWrapper pipeline and reproduces
	 * the original innerContent structure faithfully.
	 *
	 * @return string
	 */
	public function render(): string {
		$this->build();

		$comments = new BlockComments( $this->blockName, $this->blockAttributes );
		$children = $this->getChildren();

		if ( empty( $this->innerContent ) ) {
			if ( empty( $children ) ) {
				return $comments->selfClosingComment();
			}

			$inner = '';
			foreach ( $children as $child ) {
				$inner .= is_string( $child ) ? $child : (string) $child;
			}

			return '' === trim( $inner )
				? $comments->selfClosingComment()
				: $comments->wrapContent( $inner );
		}

		$inner      = '';
		$childIndex = 0;

		foreach ( $this->innerContent as $chunk ) {
			if ( null === $chunk ) {
				$child  = $children[ $childIndex++ ] ?? '';
				$inner .= is_string( $child ) ? $child : (string) $child;
			} else {
				$inner .= $chunk;
			}
		}

		return '' === trim( $inner )
			? $comments->selfClosingComment()
			: $comments->wrapContent( $inner );
	}

	/**
	 * Ensure innerContent has enough child slots (null placeholders).
	 *
	 * New slots are inserted before the trailing chunk so additional children
	 * stay inside the original wrapper structure.
	 *
	 * @return void
	 */
	private function ensureInnerContentSlotsForChildren(): void {
		if ( empty( $this->innerContent ) ) {
			return;
		}

		$slotCount      = count(
			array_filter(
				$this->innerContent,
				static fn ( mixed $chunk ): bool => null === $chunk
			)
		);
		$childrenCount  = count( $this->children );
		$missingSlots   = max( 0, $childrenCount - $slotCount );
		$insertionIndex = max( count( $this->innerContent ) - 1, 0 );

		if ( 0 === $missingSlots ) {
			return;
		}

		array_splice( $this->innerContent, $insertionIndex, 0, array_fill( 0, $missingSlots, null ) );
	}
}
