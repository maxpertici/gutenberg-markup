<?php

namespace MaxPertici\GutenbergMarkup\Concerns\Dimensions;

trait PaddingTrait {
    /**
     * @var array
     */
    protected $padding = [
        'top' => null,
        'right' => null,
        'bottom' => null,
        'left' => null,
    ];

    /**
     * Set all padding for the block.
     * 
     * @param null|string $top
     * @param null|string $right
     * @param null|string $bottom
     * @param null|string $left
     * @return $this
     */
    public function padding( ?string $top = null, ?string $right = null, ?string $bottom = null, ?string $left = null ): self
    {
        $this->padding = [
            'top' => $top,
            'right' => $right,
            'bottom' => $bottom,
            'left' => $left,
        ];
        $this->addStylesOnBlock();
        return $this;
    }

    /**
     * Set padding top value
     *
     * @param string $value
     * @return self
     */
    public function paddingTop( string $value ): self
    {
        $this->padding['top'] = $value;
        $this->addStylesOnBlock();
        return $this;
    }

    /**
     * Set padding right value
     *
     * @param string $value
     * @return self
     */
    public function paddingRight( string $value ): self
    {
        $this->padding['right'] = $value;
        $this->addStylesOnBlock();
        return $this;
    }

    /**
     * Set padding bottom value
     *
     * @param string $value
     * @return self
     */
    public function paddingBottom( string $value ): self
    {
        $this->padding['bottom'] = $value;
        $this->addStylesOnBlock();
        return $this;
    }

    /**
     * Set padding left value
     *
     * @param string $value
     * @return self
     */
    public function paddingLeft( string $value ): self
    {
        $this->padding['left'] = $value;
        $this->addStylesOnBlock();
        return $this;
    }


    /**
     * Add Padding Attributes On Block Markup
     *
     * @return self
     */
    public function addPaddingBlockAttributes(): self
    {
        $attributes = array_filter($this->padding, function($value) {
            return !is_null($value);
        });

        $this->blockAttributes['style'] = $this->blockAttributes['style'] ?? [];
        $this->blockAttributes['style']['spacing'] = $this->blockAttributes['style']['spacing'] ?? [];
        $this->blockAttributes['style']['spacing']['padding'] = $attributes;
        return $this;
    }

    /**
     * Generate inline padding styles for block Markup
     *
     * @return string
     */
    public function inlinedPaddingStyles(): string
    {
        $inlineStyles = '';
        foreach ($this->padding as $side => $value) {
            if (!is_null($value)) {
                $inlineStyles .= "padding-{$side}: {$this->renderPaddingValue($value)}; ";
            }
        }
        return $inlineStyles;
    }

    /**
     * Convert padding value as css inline value
     *
     * @param string $value
     * @return string
     */
    private function renderPaddingValue( string $value ) :string {

        $units = ['px', 'em', 'rem', '%', 'vw', 'vh'];

        // Is unit less value?
        foreach( $units as $unit ) {
            if( str_contains( $value, $unit ) ) {
                return $value;
            }
        }
        $extractedValue = str_replace( ['var|preset|spacing|'], '', $value );
        return "var(--wp--preset--spacing--{$extractedValue})";
    }

    /**
     * Add Styles On Block Markup
     *
     * @return self
     */
    public function addStylesOnBlock(): self
    {
        $this->addPaddingBlockAttributes();

        // Catch existing styles and append padding styles
        $existingStyles = $this->getAttribute('style') ?? '';
        $this->setAttribute('style', $existingStyles . $this->inlinedPaddingStyles());
        return $this;
    }
}