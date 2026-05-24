<?php

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\AnchorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Advanced\CustomClassTrait;
use MaxPertici\GutenbergMarkup\Concerns\Block\BlockStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Block\InnerBlocksSupportTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\BackgroundColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\LinkColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Color\TextColorTrait;
use MaxPertici\GutenbergMarkup\Concerns\Dimensions\MarginTrait;
use MaxPertici\GutenbergMarkup\Concerns\Dimensions\PaddingTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontSizeTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontStyleTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\FontWeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LetterSpacingTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\LineHeightTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextDecorationTrait;
use MaxPertici\GutenbergMarkup\Concerns\Typography\TextTransformTrait;

class ListBlock extends BlockMarkup {

	use AnchorTrait;
	use BackgroundColorTrait;
	use CustomClassTrait;
	use InnerBlocksSupportTrait;
	use TextColorTrait;
	use FontSizeTrait;
	use FontStyleTrait;
	use FontWeightTrait;
	use LetterSpacingTrait;
	use LineHeightTrait;
	use LinkColorTrait;
	use TextDecorationTrait;
	use TextTransformTrait;
	use BlockStyleTrait;
	use MarginTrait;
	use PaddingTrait;

	/**
	 * List Tag
	 *
	 * @var string
	 */
    protected string $tag = 'ul';

	/**
	 * Is list ordered ?
	 *
	 * @var boolean
	 */
	protected bool $ordered = false;

	/**
	 * List Type
	 *
	 * @var string
	 */
    protected string $type = 'decimal';

	/**
	 * Allowed Types for list type attribute
	 *
	 * @var array
	 */
	protected array $allowedTypes = [ 'decimal', 'lower-alpha', 'upper-alpha', 'lower-roman', 'upper-roman' ];

	/**
	 * Start list item value
	 *
	 * @var string
	 */
	protected string $start = '';

	/**
	 * Is reversed list ?
	 *
	 * @var boolean
	 */
	protected bool $reversed = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items       The list items.
	 * @param bool  $ordered     Optional. Whether the list is ordered. Default false.
     * @param string $type       Optional. The list type. Default 'decimal'.
     * @param string $start      Optional. The start value for the list. Default ''.
     * @param bool   $reversed   Optional. Whether the list is reversed. Default false.
	 */
	public function __construct( array $items, bool $ordered = false, string $type = 'decimal', string $start = '', bool $reversed = false ) {

		// Verify items are either strings or instances of ListItemBlock or ListBlock
		$items = array_map( function( $item ) {
			if ( is_string( $item ) ) {
				return new ListItemBlock( content: $item );
			}
			if( ! $item instanceof ListItemBlock && ! $item instanceof ListBlock ) {
				return null;
			}
			return $item;
		}, $items );
		$items = array_filter( $items );

		$this->ordered = $ordered;
		$this->type = in_array( $type, $this->allowedTypes ) ? $type : $this->type;
		$this->start = $start;
		$this->reversed = $reversed;

		parent::__construct(
			blockName: 'core/list',
			children: [ ...$items ]
		);
	}

	/**
	 * Set whether the list is ordered.
	 *
	 * @param boolean $ordered
	 * @return self
	 */
	public function isOrdered( bool $ordered = true ): self {
		$this->ordered = $ordered;
		return $this;
	}

	/**
	 * Set list type
	 *
	 * @param string $type
	 * @return self
	 */
	public function listType( string $type = 'decimal' ): self {
		if( in_array( $type, $this->allowedTypes ) ) {
			$this->type = $type;
		}
		return $this;
	}

	/**
	 * Set list start
	 *
	 * @param string $start
	 * @return self
	 */
	public function start( string $start = '' ): self {
		$this->start = $start;
		return $this;
	}

	/**
	 * Set whether the list is reversed.
	 *
	 * @param boolean $reversed
	 * @return self
	 */
	public function isReversed( bool $reversed = true ): self {
		$this->reversed = $reversed;
		return $this;
	}

	/**
	 * Builds the wrapper and block attributes before rendering.
	 *
	 * Generates the wrapper HTML with the correct heading level and updates
	 * the block attributes with the level if it's not the default (2).
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function build(): void {
		if( $this->ordered ) {
			$this->tag = 'ol';
			$this->setBlockAttributes( [ 'ordered' => true ] );
		}
		if( $this->type !== 'decimal' ) {
			$this->setBlockAttributes( [ 'type' => $this->type ] );
		}
		if( $this->start !== '' ) {
			$this->setBlockAttributes( [ 'start' => $this->start ] );
			$this->setAttribute( 'start', $this->start );
		}
		if( $this->reversed ) {
			$this->setBlockAttributes( [ 'reversed' => true ] );
			$this->setAttribute( 'reversed', 'reversed' );
		}
        $this->wrapper = "<{$this->tag} class=\"%classes%\" %attributes%>%children%</{$this->tag}>";
	}

	/**
	 * Hydrate runtime state from parsed attrs.
	 *
	 * @param array $attributes Parsed Gutenberg attrs.
	 * @param bool  $merge Merge or replace attributes.
	 * @return self
	 */
	public function hydrate( array $attributes, bool $merge = false ): self {
		parent::hydrate( $attributes, $merge );

		if ( array_key_exists( 'ordered', $attributes ) ) {
			$this->isOrdered( (bool) $attributes['ordered'] );
		}

		if ( isset( $attributes['type'] ) ) {
			$this->listType( (string) $attributes['type'] );
		}

		if ( isset( $attributes['start'] ) ) {
			$this->start( (string) $attributes['start'] );
		}

		if ( array_key_exists( 'reversed', $attributes ) ) {
			$this->isReversed( (bool) $attributes['reversed'] );
		}

		return $this;
	}
}
