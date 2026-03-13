<?php

namespace MaxPertici\GutenbergMarkup\Concerns\Dimensions;


trait BlockSpacingTrait
{
    /**
     * @var string|null
     */
    protected ?string $blockSpacing = null;

    /**
     * @param null|string $blockSpacing
     * @return $this
     */
    public function blockSpacing(?string $blockSpacing): static
    {
        $this->blockSpacing = $blockSpacing;
        $this->blockAttributes['style'] = $this->blockAttributes['style'] ?? [];
        $this->blockAttributes['style']['spacing'] = $this->blockAttributes['style']['spacing'] ?? [];
        $this->blockAttributes['style']['spacing']['blockGap'] = $this->renderGapValue( $this->blockSpacing );
        return $this;
    }

    /**
     * Convert gap value
     *
     * @param string $value
     * @return string
     */
    private function renderGapValue( string $value ) :string {

        $units = ['px', 'em', 'rem', '%', 'vw', 'vh'];

        // Is unit less value?
        foreach( $units as $unit ) {
            if( str_contains( $value, $unit ) ) {
                return $value;
            }
        }

        if( str_starts_with( $value, 'var:preset|spacing|' ) ){
            return $value;
        }

        return "var:preset|spacing|{$value}";
    }
}