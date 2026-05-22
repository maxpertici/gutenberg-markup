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
- Les blocks non supportés restent en markup simple (fallback) avec commentaires Gutenberg conservés.
- Les attributs inconnus sont conservés.

## Résolution auto + mapping custom

La factory tente, dans cet ordre :

1. Mapping local passé à `parsePostContent(...)`
2. Mapping global enregistré via `BlockFactory::registerBlockParser(...)`
3. Résolution native des blocks supportés de la lib
4. Résolution automatique par convention de nom (`core/list-item` -> `Blocks\\ListItemBlock`)
5. Fallback markup simple

Exemple avec mapping local :

```php
use MaxPertici\GutenbergMarkup\BlockFactory;
use App\Blocks\HeroBlock;

$blocks = BlockFactory::parsePostContent(
	$content,
	[
		'myplugin/hero' => HeroBlock::class,
		'core/group' => App\Blocks\ExtendedGroupBlock::class,
	]
);
```

> Un mapping peut remplacer un block natif déjà supporté (ex: `core/group`).  
> Pour un resolver class-string, si le 1er argument du constructeur accepte un `array`, la factory injecte automatiquement les children parsés du block.

Exemple avec parser custom :

```php
BlockFactory::registerBlockParser(
	'myplugin/hero',
	fn ( array $parsedBlock, array $attrs ) => new HeroBlock( $parsedBlock, $attrs )
);
```

> Important : si un block parent supporté contient des children non compatibles avec son API (ex: `core/columns` avec un enfant non `core/column`), la factory bascule ce parent en fallback markup pour préserver un rendu Gutenberg propre et sans perte.

## Travailler le post content bloc par bloc

`PostContent` permet de manipuler une matière première structurée (arbre de blocs parsés), à partir d’un markup Gutenberg ou d’un array déjà parsé.

```php
use MaxPertici\GutenbergMarkup\PostContent;

$postContent = new PostContent( $rawGutenbergMarkup );
// ou: new PostContent( $alreadyParsedBlocksArray );

$group = $postContent->findFirst( 'core/group' );

$postContent->updateAll(
	'core/group',
	function ( array $block ): array {
		$attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : [];
		$attrs['className'] = trim( ( $attrs['className'] ?? '' ) . ' is-style-my-extended-group' );
		$block['attrs']     = $attrs;

		return $block;
	}
);

$updatedMarkup = $postContent->toMarkup();
```

Méthodes utiles :
- `findFirst( $blockName )`
- `findAll( $blockName )`
- `updateFirst( $blockName, $updater )`
- `updateAll( $blockName, $updater )`
- `toBlocks()` pour obtenir les blocks typés de la lib
- `toMarkup()` pour reconstruire le markup Gutenberg
