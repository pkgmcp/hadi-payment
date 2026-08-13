# Release Checklist — v1.0.0

This document is the M7 release-readiness checklist. Repo-local preparation
is complete; only the external distribution steps remain and they require a
maintainer account on Packagist.

## Repo-local (complete)

- [x] `CHANGELOG.md` written (Keep a Changelog format, version `1.0.0`).
- [x] `composer.json` valid (`composer validate`), license MIT, `hadi/hadi-payment`.
- [x] Quality gates green: PHPUnit 175 tests / 4667 assertions, PHPStan level 5
  zero errors, PHPCS PSR-12 zero errors, `php -l` clean.
- [x] Docs updated: `docs/PRD.md` (completeness audit, score) and `docs/html/index.html`.

## Tagging (maintainer)

1. Run the final quality gates:

   ```
   composer test
   composer stan
   composer cs
   composer validate
   ```

2. Commit any remaining changes and create the tag:

   ```
   git tag -a v1.0.0 -m "Release v1.0.0"
   git push origin v1.0.0
   ```

## Packagist publication (maintainer, external)

3. Create a Packagist account and confirm the email.
4. Submit the repository at https://packagist.org/packages/submit
   (GitHub/webhook or manual `composer update hadi/hadi-payment` to verify).
5. Set the Packagist service/webhook on the repository so new tags auto-publish.

## Community review (external)

6. Announce the release and invite review of the public API, the generated
   catalog, and the 60+ concrete driver flows.

## Verification after release

- `composer require hadi/hadi-payment` resolves to `1.0.0`.
- `CountryCatalog` loads all 195 countries and per-country top lists.
- Smoke-test one hosted gateway (e.g. bKash) end to end with live credentials.
