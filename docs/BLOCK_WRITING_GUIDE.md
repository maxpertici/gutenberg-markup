# Guide d’écriture d’un block

Ce document définit la convention à suivre pour implémenter un block dans cette librairie.

## 1) Déclarer explicitement le support des enfants

Chaque block doit déclarer **exactement un** des traits suivants :

- `InnerBlocksSupportTrait` : le block accepte des enfants.
- `SelfClosingBlockSupportTrait` : le block n’accepte pas d’enfants.

Ne pas laisser le comportement implicite.

## 2) Un attribut Gutenberg = une propriété runtime

Tout attribut métier du block doit avoir une propriété dédiée dans la classe (ex: `content`, `url`, `level`, `layoutType`).

Objectif :

- garder un état objet clair,
- éviter de manipuler directement `blockAttributes` partout,
- rendre l’API lisible côté développeur.

## 3) Constructeur simple et minimaliste

Le constructeur doit rester minimal :

- recevoir uniquement les données indispensables à l’instanciation,
- initialiser les propriétés de base,
- déléguer l’initialisation technique à `parent::__construct(...)`,
- éviter la logique complexe dans le constructeur.

La composition finale du rendu se fait dans `build()`, pas dans le constructeur.

## 4) `build()` construit l’état final du block

La méthode `build()` est la source de vérité pour préparer l’état final avant `render()` / `print()` :

- calcule les `blockAttributes` finaux à partir des propriétés,
- ajoute/supprime les attributs selon les valeurs effectives,
- prépare wrapper/classes/attributs HTML nécessaires au rendu.

`build()` doit être idempotente (appelable plusieurs fois sans dérive).

## 5) Exposer des méthodes de manipulation (get/set fluide)

Chaque propriété métier doit avoir une méthode publique pour lecture/écriture, avec API fluide (`return $this` en mode setter).

Ces méthodes sont l’API officielle de mutation du block.

## 6) `hydrate()` reconstruit le block depuis les attrs

Chaque block doit implémenter `hydrate(array $attributes, bool $merge = false): self` pour reconstruire son état runtime depuis les attributs parsés :

- appeler `parent::hydrate(...)` en premier,
- mapper explicitement les attributs vers les propriétés via les méthodes publiques,
- couvrir **tous** les attributs du block (pas seulement une partie),
- conserver la cohérence entre propriétés et `blockAttributes`.

## 7) Flux attendu pour un block

1. Instanciation minimale via le constructeur.
2. Mutation via méthodes métier.
3. `build()` prépare l’état final.
4. `render()` / `print()` produisent le markup Gutenberg.
5. `hydrate()` permet le chemin inverse depuis des attributs parsés.

## 8) Règle de validation pour une nouvelle classe de block

Avant de considérer un block terminé, vérifier :

- [ ] Trait enfant explicite (`InnerBlocksSupportTrait` ou `SelfClosingBlockSupportTrait`)
- [ ] Une propriété par attribut métier
- [ ] Constructeur minimaliste
- [ ] `build()` centralise l’état final
- [ ] Méthodes publiques de manipulation disponibles
- [ ] `hydrate()` mappe l’ensemble des attributs utiles
- [ ] Règles sécurité respectées (voir `docs/SECURITY.md`)

## 9) Exigences sécurité minimales

Pour toute nouvelle classe de block :

- échapper systématiquement texte + attributs HTML ;
- sanitiser toute URL avant rendu ;
- n’autoriser le HTML brut que dans les zones explicitement prévues ;
- garantir un comportement sûr même hors runtime WordPress (pas de fatal sur fonctions WP absentes).
