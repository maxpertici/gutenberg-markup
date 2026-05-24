<?php
/**
 * Self-Closing Block Support Trait
 *
 * Shared helper for blocks that should reject children mutation APIs.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Block
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Block;

trait SelfClosingBlockSupportTrait {

	/**
	 * Return whether this block can contain children.
	 *
	 * @return bool
	 */
	public function supportsChildren(): bool {
		return false;
	}
}
