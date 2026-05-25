# Security model & sanitization policy

Ce document définit la politique sécurité de la librairie.

## 1) Trust model (entrée)

- **Trusted WordPress context** : lorsque la librairie est utilisée avec les helpers WP (`parse_blocks`, `esc_*`, `wp_kses_post`), les fonctions natives WordPress sont la source de vérité.
- **Untrusted external input** : toute donnée venant d’API externes, formulaires, imports ou scripts doit être considérée non fiable.

Règle : les blocks doivent rester sûrs même hors runtime WordPress.

## 2) Politique de sanitization

- **Texte rendu dans le HTML** : toujours échappé.
- **Valeurs d’attributs HTML** : toujours échappées.
- **URLs** : toujours sanitizées (allowlist schémas sûrs) puis échappées.
- **HTML autorisé** : seulement dans les endroits explicitement prévus (ex. contenu rich text). Sinon, texte simple échappé.

## 3) Politique d’échappement

Utiliser les helpers centralisés de `BlockMarkup` :

- `escapeText()`
- `escapeAttribute()`
- `sanitizeUrl()`
- `escapeUrlAttribute()`

Ne pas introduire de nouveaux `htmlspecialchars(...)` isolés dans les blocks.

## 4) Checklist sécurité obligatoire (nouveau block)

Avant merge :

- [ ] Toutes les sorties texte/attributs passent par les helpers centralisés
- [ ] Toutes les URLs sont sanitizées + échappées
- [ ] Les entrées “HTML libre” sont limitées aux cas explicitement autorisés
- [ ] Le block fonctionne sans fatal error hors runtime WordPress
- [ ] Un test unitaire couvre au moins un cas d’entrée malveillante/malformée
