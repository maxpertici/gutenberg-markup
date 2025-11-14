<?php
/**
 * Block Markup for Gutenberg Blocks
 *
 * Base class for Gutenberg block markup generation.
 *
 * @package MaxPertici\GutenbergMarkup
 */

namespace MaxPertici\GutenbergMarkup;

use MaxPertici\Markup\Markup;

/**
 * Base class for Gutenberg block markup generation.
 *
 * Extends the Markup class to add Gutenberg block comment support.
 * Automatically wraps the generated markup with appropriate Gutenberg block comments.
 * This class facilitates the creation of Gutenberg blocks programmatically by handling
 * the block comment syntax required by the WordPress block editor.
 *
 * Example usage:
 * ```php
 * $block = new BlockMarkup(
 *     'core/paragraph',
 *     ['align' => 'center'],
 *     false,
 *     '<p>{children}</p>'
 * );
 * ```
 *
 * @since 1.0.0
 */
class BlockMarkup extends Markup {

	/**
	 * The block name (e.g., 'core/paragraph', 'acf/my-block').
	 *
	 * This property stores the full block identifier used by Gutenberg,
	 * including the namespace and block name separated by a slash.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $block_name;

	/**
	 * The block attributes.
	 *
	 * An associative array containing all block attributes that will be
	 * serialized into the Gutenberg block comment syntax (JSON format).
	 * These attributes are used by the block editor to maintain state.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected array $block_attributes;

	/**
	 * Whether this is a self-closing block (no inner content).
	 *
	 * When true, the block will be rendered as a self-closing comment
	 * without opening and closing tags. This is useful for blocks that
	 * don't contain any inner content or children.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected bool $is_self_closing;

	/**
	 * The BlockComments instance.
	 *
	 * This instance handles the generation of Gutenberg block comment syntax,
	 * including opening comments, closing comments, and self-closing comments.
	 *
	 * @since 1.0.0
	 * @var BlockComments
	 */
	private BlockComments $block_comments;

	/**
	 * Constructor.
	 *
	 * Initializes a new BlockMarkup instance with Gutenberg block support.
	 * Sets up the parent Markup properties and creates a BlockComments instance
	 * to handle the Gutenberg comment syntax.
	 *
	 * @since 1.0.0
	 *
	 * @param string $block_name         The Gutenberg block name (e.g., 'core/paragraph', 'acf/hero-block').
	 * @param array  $block_attributes   Optional. Block attributes as key-value pairs. Default empty array.
	 * @param bool   $is_self_closing    Optional. Whether block is self-closing (no inner content). Default false.
	 * @param string $wrapper            Optional. HTML wrapper template with {children} placeholder. Default empty string.
	 * @param array  $wrapper_class      Optional. CSS classes to apply to the wrapper element. Default empty array.
	 * @param array  $wrapper_attributes Optional. HTML attributes for the wrapper element. Default empty array.
	 * @param string $children_wrapper   Optional. HTML wrapper template for children elements. Default empty string.
	 * @param array  $children           Optional. Array of child Markup objects or strings. Default empty array.
	 * @param string $path               Optional. Path identifier for tree walking and debugging. Default empty string.
	 */
	public function __construct(
		string $block_name,
		array $block_attributes = [],
		bool $is_self_closing = false,
		string $wrapper = '',
		array $wrapper_class = [],
		array $wrapper_attributes = [],
		string $children_wrapper = '',
		array $children = [],
		string $path = ''
	) {
		// Initialize parent Markup
		parent::__construct(
			$wrapper,
			$wrapper_class,
			$wrapper_attributes,
			$children_wrapper,
			$children,
			$path
		);

		// Initialize block-specific properties
		$this->block_name       = $block_name;
		$this->block_attributes = $block_attributes;
		$this->is_self_closing  = $is_self_closing;

		// Create BlockComments instance
		$this->block_comments = new BlockComments( $block_name, $block_attributes );
	}

	/**
	 * Gets the complete block markup with Gutenberg comments.
	 *
	 * Wraps the parent markup with appropriate Gutenberg block comments.
	 * For self-closing blocks, returns only the self-closing comment.
	 * For regular blocks, wraps the inner HTML content with opening and closing block comments.
	 *
	 * This method overrides the parent getMarkup() to add Gutenberg-specific formatting.
	 *
	 * @since 1.0.0
	 *
	 * @return string The complete block markup including Gutenberg comment syntax.
	 */
	public function getMarkup(): string {
		// Update the BlockComments instance with current attributes
		$this->block_comments = new BlockComments( $this->block_name, $this->block_attributes );

		// If self-closing block, return only the comment
		if ( $this->is_self_closing ) {
			return $this->block_comments->get_self_closing_comment();
		}

		// Get the inner markup from parent class
		$inner_markup = parent::getMarkup();

		// Wrap with block comments
		return $this->block_comments->wrap_content( $inner_markup );
	}

	/**
	 * Prints the complete block markup with Gutenberg comments (echo mode).
	 *
	 * Outputs the block with appropriate Gutenberg block comments directly to the buffer.
	 * This method is optimized for performance by echoing content in streaming mode
	 * instead of building the entire string in memory first.
	 *
	 * For self-closing blocks, echoes only the self-closing comment.
	 * For regular blocks, echoes the opening comment, inner content, and closing comment separately.
	 *
	 * This method overrides the parent print() to add Gutenberg-specific formatting.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function print(): void {
		// Update the BlockComments instance with current attributes
		$this->block_comments = new BlockComments( $this->block_name, $this->block_attributes );

		// If self-closing block, echo only the comment
		if ( $this->is_self_closing ) {
			echo $this->block_comments->get_self_closing_comment();
			return;
		}

		// Echo opening block comment
		echo $this->block_comments->get_opening_comment();

		// Print inner content using parent's print method
		parent::print();

		// Echo closing block comment
		echo $this->block_comments->get_closing_comment();
	}

	/**
	 * Gets the block name.
	 *
	 * Returns the fully qualified block name including namespace
	 * (e.g., 'core/paragraph', 'acf/hero-block').
	 *
	 * @since 1.0.0
	 *
	 * @return string The block name with namespace.
	 */
	public function get_block_name(): string {
		return $this->block_name;
	}

	/**
	 * Gets the block attributes.
	 *
	 * Returns the complete array of block attributes that will be
	 * serialized into the Gutenberg block comment. These attributes
	 * maintain the block's state in the editor.
	 *
	 * @since 1.0.0
	 *
	 * @return array The block attributes as an associative array.
	 */
	public function get_block_attributes(): array {
		return $this->block_attributes;
	}

	/**
	 * Sets or updates block attributes.
	 *
	 * Allows updating the block attributes after instantiation.
	 * When merging, new attributes are combined with existing ones,
	 * with new values overwriting existing keys.
	 *
	 * After updating attributes, the internal BlockComments instance
	 * is recreated to reflect the new attribute values in the comment syntax.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes The attributes to set or merge as key-value pairs.
	 * @param bool  $merge      Optional. Whether to merge with existing attributes (true)
	 *                          or replace them entirely (false). Default true.
	 * @return void
	 */
	public function set_block_attributes( array $attributes, bool $merge = true ): void {
		if ( $merge ) {
			$this->block_attributes = array_merge( $this->block_attributes, $attributes );
		} else {
			$this->block_attributes = $attributes;
		}

		// Update the BlockComments instance
		$this->block_comments = new BlockComments( $this->block_name, $this->block_attributes );
	}

	/**
	 * Gets the BlockComments instance.
	 *
	 * Provides access to the internal BlockComments object that handles
	 * the generation of Gutenberg block comment syntax. This can be useful
	 * for advanced manipulation or when you need direct access to comment
	 * generation methods.
	 *
	 * @since 1.0.0
	 *
	 * @return BlockComments The BlockComments instance used by this block.
	 */
	public function get_block_comments(): BlockComments {
		return $this->block_comments;
	}

	/**
	 * Adds a CSS class to the wrapper element.
	 *
	 * If the class already exists, it will not be added again.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The CSS class to add.
	 * @return void
	 */
	protected function add_class( string $class ): void {
		if ( ! in_array( $class, $this->wrapper_class, true ) ) {
			$this->wrapper_class[] = $class;
		}
	}

	/**
	 * Removes a CSS class from the wrapper element.
	 *
	 * @since 1.0.0
	 *
	 * @param string $class The CSS class to remove.
	 * @return void
	 */
	protected function remove_class( string $class ): void {
		$this->wrapper_class = array_values(
			array_filter(
				$this->wrapper_class,
				function ( $existing_class ) use ( $class ) {
					return $existing_class !== $class;
				}
			)
		);
	}
}

