# Tests

La bibliothèque utilise désormais une suite **PHPUnit** automatisée.

## Exécution

Depuis la racine du projet :

```bash
composer install
composer test
```

## Couverture actuelle (priorité risques)

- fallback des blocs non supportés (`PostContentBlock`)
- parsing de blocs imbriqués activés
- robustesse face aux payloads malformés
- non-régression du flux `PostContent` collection-first (`toBlocksCollection()` + `withBlocks()`)
- règles de sanitization/escaping sur les blocs qui rendent du HTML manuel (`ButtonBlock`, `FileBlock`)

## Notes

- `tests/bootstrap.php` fournit des fonctions WordPress minimales de test (`parse_blocks`, `do_blocks`) quand WP n’est pas disponible.
- Ces stubs sont destinés aux tests unitaires de la bibliothèque, pas à reproduire tout le runtime WordPress.
