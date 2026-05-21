<?php

namespace MaxPertici\GutenbergMarkup\Blocks;

use MaxPertici\GutenbergMarkup\BlockMarkup;

class ListItemBlock extends BlockMarkup {

	/**
	 * List Item Tag
	 *
	 * @var string
	 */
    protected string $tag = 'li';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content      The list item content.
	 */
	public function __construct( string $content ) {

		parent::__construct(
			blockName: 'core/list-item',
			blockAttributes: array(),
			children: [ $content ]
		);

        $this->wrapper = "<{$this->tag} class=\"%classes%\" %attributes%>%children%</{$this->tag}>";
	}
}

