<?php

namespace MaxPertici\GutenbergMarkup\Concerns\Dimensions;

trait MarginTrait {
    /**
     * @var array
     */
    protected $margin = [
        'top' => null,
        'right' => null,
        'bottom' => null,
        'left' => null,
    ];

    /**
     * Set all margin for the block.
     * 
     * @param null|string $top
     * @param null|string $right
     * @param null|string $bottom
     * @param null|string $left
     * @return $this
     */
    public function margin( ?string $top = null, ?string $right = null, ?string $bottom = null, ?string $left = null ): self
    {
        $this->margin = [
            'top' => $top,
            'right' => $right,
            'bottom' => $bottom,
            'left' => $left,
        ];
        $this->addMarginStylesOnBlock();
        return $this;
    }

    /**
     * Set margin top value
     *
     * @param string $value
     * @return self
     */
    public function marginTop( string $value ): self
    {
        $this->margin['top'] = $value;
        $this->addMarginStylesOnBlock();
        return $this;
    }

    /**
     * Set margin right value
     *
     * @param string $value
     * @return self
     */
    public function marginRight( string $value ): self
    {
        $this->margin['right'] = $value;
        $this->addMarginStylesOnBlock();
        return $this;
    }

    /**
     * Set margin bottom value
     *
     * @param string $value
     * @return self
     */
    public function marginBottom( string $value ): self
    {
        $this->margin['bottom'] = $value;
        $this->addMarginStylesOnBlock();
        return $this;
    }

    /**
     * Set margin left value
     *
     * @param string $value
     * @return self
     */
    public function marginLeft( string $value ): self
    {
        $this->margin['left'] = $value;
        $this->addMarginStylesOnBlock();
        return $this;
    }


    /**
     * Add Margin Attributes On Block Markup
     *
     * @return self
     */
    public function addMarginBlockAttributes(): self
    {
        $attributes = array_filter($this->margin, function($value) {
            return !is_null($value);
        });

        $this->blockAttributes['style'] = $this->blockAttributes['style'] ?? [];
        $this->blockAttributes['style']['spacing'] = $this->blockAttributes['style']['spacing'] ?? [];
        $this->blockAttributes['style']['spacing']['margin'] = $attributes;
        return $this;
    }

    /**
     * Generate inline margin styles for block Markup
     *
     * @return string
     */
    public function inlinedMarginStyles(): string
    {
        $inlineStyles = '';
        foreach ($this->margin as $side => $value) {
            if (!is_null($value)) {
                $inlineStyles .= "margin-{$side}: {$this->renderMarginValue($value)}; ";
            }
        }
        return $inlineStyles;
    }

    /**
     * Convert margin value as css inline value
     *
     * @param string $value
     * @return string
     */
    private function renderMarginValue( string $value ) :string {

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
    public function addMarginStylesOnBlock(): self
    {
        $this->addMarginBlockAttributes();

        // Catch existing styles and append margin styles
        $existingStyles = $this->getAttribute('style') ?? '';
        $this->setAttribute('style', $existingStyles . $this->inlinedMarginStyles());
        return $this;
    }
}
