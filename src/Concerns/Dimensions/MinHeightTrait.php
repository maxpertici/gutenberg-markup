<?php

namespace MaxPertici\GutenbergMarkup\Concerns\Dimensions;

trait MinHeightTrait
{
    /**
     * @var null|string
     */
    protected ?string $minHeight = null;

    /**
     * @param string|null $minHeight
     * @return $this
     */
    public function minHeight(?string $minHeight): self
    {
        $this->minHeight = $minHeight;
        $this->blockAttributes['dimensions'] = $this->blockAttributes['dimensions'] ?? [];
        $this->blockAttributes['dimensions']['minHeight'] = $this->minHeight;
        return $this;
    }
}