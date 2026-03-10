<?php
/**
 * Block Style Trait
 *
 * Provides block style functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Block
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Block;

/**
 * Block Style Trait.
 *
 * Add block style support to Gutenberg blocks
 *
 * @since 1.0.0
 */
trait BlockStyleTrait {

    public function blockStyle( string $styleName ): self {
        $styleName = $this->normalizedStyleName( $styleName );

        // Clean already defined block style class
        array_filter( $this->classes(), function( $class ) {
            return str_contains( $class, 'is-style-' ) ? false : true;
        } );

        $this->addClass( "is-style-{$styleName}" );
        return $this;
    }

    /**
     * Normalize block style className
     *
     * @param string $styleName
     * @return string
     */
    protected function normalizedStyleName( string $styleName ): string {
        $name = trim( $styleName );
        $name = strtolower( $name );
        $name = str_replace( ' ', '-', $name );

        if( str_contains( $name, 'is-style-' ) ) {
            $name = str_replace( 'is-style-', '', $name );
        }

        return $name;
    }
}

