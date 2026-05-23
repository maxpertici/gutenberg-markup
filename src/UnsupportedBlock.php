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
			isSelfClosing: empty( $innerContent ),
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
		$this->isSelfClosing = empty( $this->innerContent );
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

		if ( empty( $this->innerContent ) ) {
			return $comments->selfClosingComment();
		}

		$inner      = '';
		$childIndex = 0;
		$children   = $this->getChildren();

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
}
