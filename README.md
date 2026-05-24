# Gutenberg Markup

Bibliothèque PHP pour écrire du markup Gutenberg (WordPress) de façon cohérente et stylable. Un outil pour produire un markup maintenable, écrire des blocs et les imbriquer comme des blocs Gutenberg, avec une approche fluide.

## Concept

- Chaque bloc Gutenberg est représenté par une classe (ex. `HeadingBlock`).
- Le markup est construit à partir d’attributs (attrs) fournis au bloc.
- Des traits (`Concerns`) factorisent les comportements communs (couleurs, typographie, alignement, etc.).
- Bonne pratique: chaque classe de bloc déclare explicitement sa capacité enfant via un trait:
  - `InnerBlocksSupportTrait` pour les blocs qui acceptent des enfants.
  - `SelfClosingBlockSupportTrait` pour les blocs sans enfants (API de mutation ignorée).

Guide détaillé d’implémentation d’un block : [`docs/BLOCK_WRITING_GUIDE.md`](docs/BLOCK_WRITING_GUIDE.md)

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

## Analyser un post Gutenberg vers des blocks

```php
use MaxPertici\GutenbergMarkup\BlockFactory;

$content = file_get_contents( __DIR__ . '/resources/post-content.html' );
$blocks  = BlockFactory::parsePostContent( $content );
```

- Les blocks supportés sont convertis en classes dédiées (`ParagraphBlock`, `HeadingBlock`, etc.).
- Les blocks non supportés restent en markup simple (fallback) avec commentaires Gutenberg conservés.
- Les attributs inconnus sont conservés.

Pour un block de post content (`PostContentBlock`), vous pouvez manipuler explicitement les enfants :

```php
use MaxPertici\GutenbergMarkup\PostContentBlock;
use MaxPertici\GutenbergMarkup\Blocks\ParagraphBlock;

$postContentBlock = new PostContentBlock( 'core/group' );
$postContentBlock
	->addChild( new ParagraphBlock( 'Enfant 1' ) )
	->addChild( new ParagraphBlock( 'Enfant 2' ) );
```

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

`PostContent` permet de manipuler un post Gutenberg via une représentation imbriquée `BlockMarkup|string`.
`PostContent` hérite de `Markup` pour offrir une API fluide orientée rendu + collections.

```php
use MaxPertici\GutenbergMarkup\PostContent;

$postContent = new PostContent( $rawGutenbergMarkup );
// ou: new PostContent( $alreadyParsedBlocksArray );

// Représentation imbriquée BlockMarkup/string (compatible Markup collections)
$blockMarkupCollection = $postContent->toBlockMarkupCollection();
$groupBlocks = $blockMarkupCollection->filter(
	fn ( $item ) => $item instanceof \MaxPertici\GutenbergMarkup\BlockMarkup
		&& 'core/group' === $item->blockName()
);

$markup = $postContent->render();        // markup Gutenberg courant
$html   = $postContent->renderBlocks();  // résultat WordPress (do_blocks) si disponible
```

Exemple — mettre à jour un seul block dans un post content :

```php
use MaxPertici\GutenbergMarkup\PostContent;
use MaxPertici\GutenbergMarkup\Blocks\HeadingBlock;

$postContent  = new PostContent( $rawGutenbergMarkup );
$blocks       = $postContent->toBlocks();

foreach ( $blocks as $index => $block ) {
	if ( ! $block instanceof HeadingBlock ) {
		continue;
	}

	$blocks[ $index ] = new HeadingBlock(
		content: 'Titre mis à jour',
		level: 3
	);

	break; // on ne modifie qu'un seul block
}

$updatedMarkup = '';
foreach ( $blocks as $block ) {
	if ( is_string( $block ) ) {
		$updatedMarkup .= $block; // fallback markup non supporté
		continue;
	}

	if ( is_object( $block ) && method_exists( $block, 'render' ) ) {
		$updatedMarkup .= $block->render();
	}
}
```

Méthodes utiles :
- `render()` et `print()` pour le markup Gutenberg courant
- `renderBlocks()` et `printBlocks()` pour le rendu final WordPress
- `parsedBlocks()` pour récupérer l’arbre parse_blocks() courant
- `toBlocks()` pour obtenir les blocks typés de la lib
- `toBlocksCollection()` pour manipuler les blocks typés comme collection
- `toMarkup()` pour reconstruire le markup Gutenberg
- `toBlockMarkup()` pour obtenir un arbre imbriqué `BlockMarkup|string`
- `toBlockMarkupCollection()` pour exploiter la représentation avec les méthodes de collection
- `withBlocks()` pour reconstruire directement un `PostContent` depuis une collection ou un tableau de blocks modifiés

Exemple — remplacer les blocks button qui ciblent une URL précise (API collection) :

```php
use MaxPertici\GutenbergMarkup\PostContent;
use MaxPertici\GutenbergMarkup\Blocks\ButtonBlock;

$postContent = new PostContent( $rawGutenbergMarkup );

$updatedBlocks = $postContent
	->toBlocksCollection()
	->map( function ( $block ) {
		if ( ! $block instanceof ButtonBlock ) {
			return $block;
		}

		if ( 'https://old.example.com' !== $block->url() ) {
			return $block;
		}

		return ( new ButtonBlock(
			content: 'CTA mis à jour',
			url: 'https://new.example.com'
		) )
			->openInNewTab( true )
			->rel( 'noopener noreferrer' );
	} );

$updatedPostContent = $postContent->withBlocks( $updatedBlocks );
$updatedBlocksArray = $updatedPostContent->toBlocks();
$updatedMarkup = $updatedPostContent->toMarkup();
```

Exemple — gérer le spacing d’un `GroupBlock` de façon fluide :

```php
use MaxPertici\GutenbergMarkup\PostContent;
use MaxPertici\GutenbergMarkup\Blocks\GroupBlock;

$postContent = new PostContent( $rawGutenbergMarkup );

$updatedBlocks = $postContent
	->toBlocksCollection()
	->map( function ( $block ) {
		if ( ! $block instanceof GroupBlock ) {
			return $block;
		}

		return $block
			->padding( 'var:preset|spacing|small' ) // padding global
			->blockSpacing( 'small' ); // gap interne du group
	} );

$updatedPostContent = $postContent->withBlocks( $updatedBlocks );
$updatedMarkup = $updatedPostContent->toMarkup();
```

Exemple — rechercher (find/query) avec les méthodes de collection :

```php
use MaxPertici\GutenbergMarkup\PostContent;
use MaxPertici\GutenbergMarkup\Blocks\ButtonBlock;

$postContent = new PostContent( $rawGutenbergMarkup );
$blocks = $postContent->toBlocksCollection();

// query: tous les buttons qui matchent l'URL
$matchingButtons = $blocks->filter(
	fn ( $block ) => $block instanceof ButtonBlock
		&& 'https://old.example.com' === $block->url()
);

// find: premier résultat de la query
$firstMatchingButton = $matchingButtons->first();
```
