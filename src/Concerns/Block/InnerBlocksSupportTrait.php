<?php
/**
 * Inner Blocks Support Trait
 *
 * Shared helpers for blocks that store and render parsed child blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Block
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Block;

trait InnerBlocksSupportTrait {

	/**
	 * Return whether this block can contain children.
	 *
	 * @return bool
	 */
	public function supportsChildren(): bool {
		return true;
	}

	/**
	 * Stored inner block children.
	 *
	 * @var array<int, object|string>
	 */
	protected array $innerBlocks = [];

	/**
	 * Replace stored inner block children.
	 *
	 * @param array<int, object|string> $children
	 * @return void
	 */
	protected function setInnerBlocks( array $children ): void {
		$this->innerBlocks = array_values( $children );
	}

	/**
	 * Return whether stored inner blocks exist.
	 *
	 * @return bool
	 */
	protected function hasInnerBlocks(): bool {
		return ! empty( $this->innerBlocks );
	}

	/**
	 * Render stored inner blocks to raw HTML.
	 *
	 * @return string
	 */
	protected function renderInnerBlocks(): string {
		$rendered = '';

		foreach ( $this->innerBlocks as $child ) {
			if ( is_string( $child ) ) {
				$rendered .= $child;
				continue;
			}

			if ( method_exists( $child, 'render' ) ) {
				$rendered .= (string) $child->render();
				continue;
			}

			$rendered .= (string) $child;
		}

		return $rendered;
	}
}
