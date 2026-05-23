# Tests - Gutenberg Markup

Ce dossier contient tous les tests et les utilitaires pour valider la bibliothèque Gutenberg Markup.

## Structure

```
tests/
├── TestPages/
│   ├── TestPageParser.php      # Parser des pages de test
│   └── TestRecap.php           # Récapitulatif des résultats
├── run-tests.php               # Script principal d'exécution
└── README.md                   # Cette documentation
```

## Pages de test

Les pages de test HTML se trouvent dans le dossier `ressources/`:

- **test-page-1-basic.html** - Contenu basique (paragraphes, listes, séparateurs)
- **test-page-2-advanced.html** - Contenu avancé (couleurs, groupes flex)
- **test-page-3-table.html** - Tableaux récapitulatifs
- **test-page-4-nested.html** - Blocs imbriqués et structures complexes
- **test-page-5-styled.html** - Styles personnalisés (typographie, espacements)
- **test-modifications-example.html** - Exemple de modifications

## Exécution des tests

### Rapport texte (par défaut)
```bash
php tests/run-tests.php
```

### Rapport JSON
```bash
php tests/run-tests.php json
```

### Rapport HTML
```bash
php tests/run-tests.php html
```

## Classes disponibles

### TestPageParser
Classe pour parser les pages HTML Gutenberg et analyser les blocs.

```php
use Maxpertici\GutenbergMarkup\Tests\TestPages\TestPageParser;

$parser = new TestPageParser();
$parser->parseAllTestPages();
$results = $parser->getResults();
```

**Méthodes principales:**
- `parseAllTestPages()` - Parse toutes les pages de test
- `parseTestPage($filename)` - Parse une page spécifique
- `generateHtmlReport()` - Génère un rapport HTML
- `generateTextReport()` - Génère un rapport texte

### TestRecap
Classe pour générer des récapitulatifs complets avec statistiques.

```php
use Maxpertici\GutenbergMarkup\Tests\TestPages\TestRecap;

$recap = new TestRecap();
$recap->runAllTests();

// Différents formats de sortie
echo $recap->displayAsText();    // Format texte
echo $recap->displayAsJson();    // Format JSON
echo $recap->displayAsHtml();    // Format HTML
```

**Méthodes principales:**
- `runAllTests()` - Exécute tous les tests
- `generateFullRecap()` - Génère un récapitulatif complet
- `displayAsHtml()` - Affiche en HTML
- `displayAsJson()` - Affiche en JSON
- `displayAsText()` - Affiche en texte

## Fichiers de sortie

Après l'exécution des tests, les rapports sont générés:

- `test-report.txt` - Rapport texte
- `test-report.json` - Rapport JSON
- `test-report.html` - Rapport HTML

## Résumé des statistiques

Les rapports incluent:

1. **Résumé**
   - Total des tests
   - Nombre de réussites/échecs
   - Taux de succès
   - Nombre de blocks parsés

2. **Détails**
   - Fichier de test
   - Statut (succès/erreur)
   - Nombre de blocks
   - Types de blocks détectés

3. **Statistiques**
   - Total de blocks parsés
   - Nombre de types uniques
   - Distribution des types

## Exemples d'utilisation

### Utilisation en ligne de commande

```bash
# Voir le rapport texte directement
php tests/run-tests.php text

# Générer un rapport JSON
php tests/run-tests.php json

# Générer un rapport HTML et l'ouvrir
php tests/run-tests.php html
open tests/test-report.html
```

### Utilisation en PHP

```php
require_once 'vendor/autoload.php';

use Maxpertici\GutenbergMarkup\Tests\TestPages\TestRecap;

$recap = new TestRecap();
$recap->runAllTests();

// Récupérer le récapitulatif complet
$fullRecap = $recap->generateFullRecap();

echo "Taux de succès: " . $fullRecap['summary']['successRate'] . "\n";
echo "Blocks parsés: " . $fullRecap['summary']['totalBlocksParsed'] . "\n";

// Parcourir les détails
foreach ($fullRecap['details'] as $filename => $detail) {
    if ($detail['status'] === 'success') {
        echo "$filename: OK (" . $detail['blockCount'] . " blocks)\n";
    }
}
```

## Notes

- Les tests comparent le contenu HTML Gutenberg avec le parsing effectué par la bibliothèque
- Les statistiques incluent tous les types de blocks supportés
- Les rapports sont détaillés pour faciliter le débogage en cas de problème
