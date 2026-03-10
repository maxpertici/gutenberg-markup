<?php
/**
 * HTML Element Style Trait
 *
 * Provides HTML element functionality for Gutenberg blocks.
 *
 * @package MaxPertici\GutenbergMarkup\Concerns\Block
 */

namespace MaxPertici\GutenbergMarkup\Concerns\Block;

/**
 * HTML Element Style Trait.
 *
 * Add HTML element support to Gutenberg blocks
 *
 * @since 1.0.0
 */
trait HtmlElementTrait {

    protected array $allowedHtmlElements = [
        'div',
        'header',
        'main',
        'section',
        'article',
        'aside',
        'footer',
    ];

    /**
     * Set the HTML element for the block wrapper.
     *
     * @param string $element The HTML element to use (e.g., 'div', 'section', 'article').
     * @return self
     */
    public function htmlElement( string $element ): self {
        if( !in_array( $element, $this->allowedHtmlElements ) ) {
            throw new \InvalidArgumentException( "Invalid HTML element: {$element}. Allowed elements are: " . implode( ', ', $this->allowedHtmlElements ) );
        }
        $this->wrapper = "<{$element} class=\"%classes%\" %attributes%>%children%</{$element}>";
        return $this;
    }
}

