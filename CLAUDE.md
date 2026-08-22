# Theme My Login

WordPress plugin providing a themed login/registration/password-recovery experience. See [README.md](README.md) for setup and [CONTRIBUTING.md](https://github.com/theme-my-login/.github/blob/main/CONTRIBUTING.md) (org-wide default) for commit/PR conventions, both required reading before making changes.

## Source of truth

- Edit under `src/`, never under `build/`. `build/` is generated (`npm run build`) and gitignored; it's what actually ships.
- The root `theme-my-login.php` loads `build/theme-my-login.php` when it exists, otherwise falls back to `src/theme-my-login.php`. Define `TML_LOAD_SOURCE` to force loading from `src/` regardless. PHPUnit always forces source loading itself (`tests/bootstrap.php`) — no build step needed before running tests.
- `src/readme.txt` is the WordPress.org plugin listing (description, FAQ, changelog) in svn-readme format. It is not this repo's README and deploys independently via `deploy-readme.yml` whenever it changes on `master`. `release-please.yml` drafts changelog entries into it automatically from `feat`/`fix`/`perf` commits (see `bin/draft-changelog.php`); check that draft over rather than editing the changelog by hand mid-release.

## Versioning

The plugin version is kept in sync by `release-please` via `x-release-please-*` markers, listed as `extra-files` in `release-please-config.json`:
- the `Version:` header in root `theme-my-login.php`
- the `Version:` header in `src/theme-my-login.php`
- the `THEME_MY_LOGIN_VERSION` constant, also in `src/theme-my-login.php`
- (extensions built on this plugin also carry a `protected $version` property with the same marker — not present in the base plugin itself)

Don't hand-bump these; version bumps come from Conventional Commits (`feat`/`fix`/`perf`) per [CONTRIBUTING.md](CONTRIBUTING.md).

## Architecture

- `Theme_My_Login` (`src/includes/class-theme-my-login.php`) is the singleton registry for actions, forms, and extensions.
- Actions (`src/includes/actions.php`, `class-theme-my-login-action.php`) define each login-related endpoint (login, register, lost password, reset password, logout, dashboard) — slug, title, callback, whether it shows on forms/nav menus.
- Forms (`class-theme-my-login-form.php`, `class-theme-my-login-form-field.php`) render the actual markup for an action.
- Extensions (`class-theme-my-login-extension.php`, `extensions.php`) is the base class the paid `tml-*` add-ons extend; `src/admin/extensions.php` renders the in-admin extensions/licenses screen.
- `src/admin/` is the wp-admin side (settings screen, extensions page); `src/includes/` is everything else.
- Multisite-specific hooks/functions are split into `ms-functions.php`/`ms-hooks.php` and tested separately under `tests/multisite/`.

## Commands

```
npm install && composer install   # once, before anything else
npm run build                      # src/ -> build/
composer test                      # phpunit (loads src/ directly)
composer lint                      # phpcs, WordPress-Extra ruleset
composer lint:fix                  # phpcbf
```

## Conventions

- WordPress Coding Standards enforced via `phpcs.xml.dist` (`WordPress-Extra` + `PHPCompatibilityWP`, PHP 7.4+ target). Run `composer lint` before considering PHP changes done.
- Commit messages are Conventional Commits, linted per-commit by CI (merges to `master` are real merge commits, not squashed). PR titles are plain English, not Conventional Commits — see [CONTRIBUTING.md](CONTRIBUTING.md) for the full rationale and the type table.
- CI workflows in `.github/workflows/` are thin callers into the shared `theme-my-login/tml-workflows` repo (consumed as the `tml-workflows` npm devDependency, which also provides `build.mjs` via `npm run build`). Repo-specific config (`phpcs.xml.dist`, `phpunit.xml.dist`, `commitlint.config.cjs`, `build.config.json`) is what actually varies per repo.
