<?php
/**
 * Block Comments Handler
 *
 * Generates Gutenberg block comment syntax.
 *
 * @package MaxPertici\GutenbergMarkup
 */

namespace MaxPertici\GutenbergMarkup;

/**
 * Handles Gutenberg block comments generation.
 *
 * Generates the opening and closing HTML comments that wrap Gutenberg blocks.
 * Format: <!-- wp:namespace/block-name {"attr":"value"} --> ... <!-- /wp:namespace/block-name -->
 *
 * @since 1.0.0
 */
class BlockComments {

	/**
	 * The block name (e.g., 'core/paragraph' or 'acf/my-block').
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $block_name;

	/**
	 * The block attributes as an associative array.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private array $attributes;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $block_name The block name (e.g., 'core/paragraph').
	 * @param array  $attributes Optional. Block attributes. Default empty array.
	 */
	public function __construct( string $block_name, array $attributes = [] ) {
		$this->block_name = $this->normalizeBlockName( $block_name );
		$this->attributes = $attributes;
	}

	/**
	 * Normalizes the block name by removing 'core/' prefix.
	 *
	 * @since 1.0.0
	 *
	 * @param string $block_name The block name.
	 * @return string The normalized block name.
	 */
	private function normalizeBlockName( string $block_name ): string {
		// Remove 'core/' prefix for core blocks
		if ( strpos( $block_name, 'core/' ) === 0 ) {
			return substr( $block_name, 5 );
		}

		return $block_name;
	}

	/**
	 * Generates the opening block comment.
	 *
	 * @since 1.0.0
	 *
	 * @return string The opening comment.
	 */
	public function openingComment(): string {
		$comment = '<!-- wp:' . $this->block_name;

		if ( ! empty( $this->attributes ) ) {
			$comment .= ' ' . json_encode( $this->attributes, JSON_UNESCAPED_SLASHES );
		}

		$comment .= ' -->';

		return $comment;
	}

	/**
	 * Generates the closing block comment.
	 *
	 * @since 1.0.0
	 *
	 * @return string The closing comment.
	 */
	public function closingComment(): string {
		return '<!-- /wp:' . $this->block_name . ' -->';
	}

	/**
	 * Generates the self-closing block comment (for blocks without content).
	 *
	 * @since 1.0.0
	 *
	 * @return string The self-closing comment.
	 */
	public function selfClosingComment(): string {
		$comment = '<!-- wp:' . $this->block_name;

		if ( ! empty( $this->attributes ) ) {
			$comment .= ' ' . json_encode( $this->attributes, JSON_UNESCAPED_SLASHES );
		}

		$comment .= ' /-->';

		return $comment;
	}

	/**
	 * Wraps content with opening and closing block comments.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content The block content to wrap.
	 * @return string The wrapped content.
	 */
	public function wrapContent( string $content ): string {
		return $this->openingComment() . $content . $this->closingComment();
	}
}

