# Gutenberg Markup

Bibliothèque PHP pour écrire du markup Gutenberg (WordPress) de façon cohérente et stylable. Un outil pour produire un markup maintenable, écrire des blocs et les imbriquer comme des blocs Gutenberg, avec une approche fluide.

## Concept

- Chaque bloc Gutenberg est représenté par une classe (ex. `HeadingBlock`).
- Le markup est construit à partir d’attributs (attrs) fournis au bloc.
- Des traits (`Concerns`) factorisent les comportements communs (couleurs, typographie, alignement, etc.).

## Exemple basique — bloc Heading

```php
use Maxpertici\GutenbergMarkup\Blocks\HeadingBlock;

$block = new HeadingBlock(
	content: 'Bonjour le monde',
	level: 2
);
$block->textColor( 'primary' );

echo $block->render();
// <!-- wp:heading {"textColor":"primary"} -->
// <h2 class="wp-block-heading has-primary-color has-text-color">Bonjour le monde</h2>
// <!-- /wp:heading -->
```

> Remarque : l’API exacte peut évoluer. Consultez les classes dans `src/Blocks` pour les options disponibles.

## Dépendance

Cette bibliothèque s’appuie sur le package Markup : https://github.com/maxpertici/markup

## Parser un post Gutenberg vers des blocks

```php
use MaxPertici\GutenbergMarkup\BlockFactory;

$content = file_get_contents( __DIR__ . '/ressources/post-content.html' );
$blocks  = BlockFactory::parsePostContent( $content );
```

- Les blocks supportés sont convertis en classes dédiées (`ParagraphBlock`, `HeadingBlock`, etc.).
- Les blocks non supportés restent en markup simple (fallback).
