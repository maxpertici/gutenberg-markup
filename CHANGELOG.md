# Changelog

## Unreleased

### Added

- Added centralized security policy in `docs/SECURITY.md` (trust model, sanitization policy, mandatory checklist for new blocks).
- Added automated PHPUnit suite for high-risk parsing/rendering and `PostContent` collection-first regression flows.
- Added GitHub Actions CI workflow (`composer validate`, PHP lint, PHPUnit).
- Added `phpunit.xml`, Composer test scripts, and `phpunit/phpunit` as dev dependency.

### Changed

- Standardized escaping/sanitization helpers in `BlockMarkup` (`escapeText`, `escapeAttribute`, `sanitizeUrl`, `escapeUrlAttribute`).
- Updated manual-render blocks (`ButtonBlock`, `FileBlock`, `QuoteBlock`, `PullquoteBlock`, `ColumnBlock`) to use shared escaping policy.
- Hardened `ImageBlock` against missing WordPress functions and sanitized generated URLs.
- Updated documentation (`README.md`, `docs/BLOCK_WRITING_GUIDE.md`, `tests/README.md`) with API guarantees, compatibility matrix, and security expectations.
