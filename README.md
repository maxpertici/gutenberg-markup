# Gutenberg Markup

A PHP library for writing Gutenberg (WordPress) markup in a consistent and stylable way. A tool to produce maintainable markup, write blocks and nest them like Gutenberg blocks, with a fluent approach.

## Concept

- Each Gutenberg block is represented by a class (e.g. `HeadingBlock`).
- The markup is built from attributes (attrs) provided to the block.
- Traits (`Concerns`) factor out common behaviors (colors, typography, alignment, etc.).

## Basic example — Heading block

```php
use Maxpertici\GutenbergMarkup\Blocks\HeadingBlock;

$block = new HeadingBlock(
	content: 'Hello world',
	level: 2
);
$block->textColor( 'primary' );
echo $block->renderBlocks();

// Note: the exact API may evolve. Check the classes in `src/Blocks` for the available options.
```

## Dependency

This library relies on the Markup package: https://github.com/maxpertici/markup
