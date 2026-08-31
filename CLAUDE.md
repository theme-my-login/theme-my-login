# Theme My Login

WordPress plugin providing a themed login/registration/password-recovery experience. See [README.md](README.md) for setup and [CONTRIBUTING.md](https://github.com/theme-my-login/.github/blob/main/CONTRIBUTING.md) (org-wide default) for commit/PR conventions, both required reading before making changes.

## Architecture

- `Theme_My_Login` (`src/includes/class-theme-my-login.php`) is the singleton registry for actions, forms, and extensions.
- Actions (`src/includes/actions.php`, `class-theme-my-login-action.php`) define each login-related endpoint (login, register, lost password, reset password, logout, dashboard) — slug, title, callback, whether it shows on forms/nav menus.
- Forms (`class-theme-my-login-form.php`, `class-theme-my-login-form-field.php`) render the actual markup for an action.
- Extensions (`class-theme-my-login-extension.php`, `extensions.php`) is the base class the paid `tml-*` add-ons extend; `src/admin/extensions.php` renders the in-admin extensions/licenses screen.
- `src/admin/` is the wp-admin side (settings screen, extensions page); `src/includes/` is everything else.

## Source of truth

- Edit under `src/`, never under `build/`. `build/` is generated (`npm run build`) and gitignored; it's what actually ships.
- The root `theme-my-login.php` loads `build/theme-my-login.php` when it exists, otherwise falls back to `src/theme-my-login.php`. Define `TML_LOAD_SOURCE` to force loading from `src/` regardless. PHPUnit always forces source loading itself (`tests/bootstrap.php`) — no build step needed before running tests.
- `src/readme.txt` is the WordPress.org plugin listing (description, FAQ, changelog) in svn-readme format. It is not this repo's README and deploys independently via `deploy-readme.yml` whenever it changes on `master`. `release-please.yml` drafts changelog entries into it automatically from `feat`/`fix`/`perf` commits (see `bin/draft-changelog.php`); check that draft over rather than editing the changelog by hand mid-release.

## Multisite

Multisite-specific code is isolated in `src/includes/ms-functions.php`/`ms-hooks.php`, loaded only when `is_multisite()`. Unlike the paid `tml-*` extensions (which are typically `Network: true`, multisite-as-default), the base plugin treats multisite as an additional mode on top of the same single-site codebase, not a separate default case.

- Network-specific actions: `signup`, `activate` (`tml_ms_register_default_actions()`), with auto-login on successful activation (`wpmu_activate_user`/`wpmu_activate_blog` → `tml_handle_auto_login()`).
- Shortcode output for the signup/activation views is filtered separately (`tml_ms_filter_signup_shortcode()`/`_activation_shortcode()`), since core's own multisite signup flow doesn't go through this plugin's normal action/form rendering.
- User-data and welcome-email filters (`tml_ms_filter_pre_insert_user_data()`, `_welcome_email()`, `_welcome_user_email()`) adjust core's own multisite registration emails/data to match this plugin's login flow.

Tested separately under `tests/multisite/`, via its own PHPUnit config (`tests/phpunit/multisite.xml`) rather than the default suite — `phpunit.xml.dist` explicitly excludes `./tests/multisite`. This file's mere existence is also what the shared `tml-workflows` CI pipeline checks to decide whether to run the multisite suite at all for a given repo — every `tml-*` extension shares this same CI job but only this base plugin currently has the file, so multisite is untested by CI everywhere else in the family.

## Conventions

- WordPress Coding Standards enforced via `phpcs.xml.dist` (`WordPress-Extra` + `PHPCompatibilityWP`, PHP 7.4+ target). Run `composer lint` before considering PHP changes done.
- Commit messages are Conventional Commits, linted per-commit by CI (merges to `master` are real merge commits, not squashed). PR titles are plain English, not Conventional Commits — see [CONTRIBUTING.md](https://github.com/theme-my-login/.github/blob/main/CONTRIBUTING.md) for the full rationale and the type table.
- CI workflows in `.github/workflows/` are thin callers into the shared `theme-my-login/tml-workflows` repo (consumed as the `tml-workflows` npm devDependency, which also provides `build.mjs` via `npm run build`). Repo-specific config (`phpcs.xml.dist`, `phpunit.xml.dist`, `commitlint.config.cjs`, `build.config.json`) is what actually varies per repo.

## i18n

Text domain `theme-my-login`, matching the plugin slug. No `load_plugin_textdomain()` call — WP's just-in-time loading means the domain must match exactly.

## Versioning

The plugin version is kept in sync by `release-please` via `x-release-please-*` markers, listed as `extra-files` in `release-please-config.json`:
- the `Version:` header in root `theme-my-login.php`
- the `Version:` header in `src/theme-my-login.php`
- the `THEME_MY_LOGIN_VERSION` constant, also in `src/theme-my-login.php`
- (extensions built on this plugin also carry a `protected $version` property with the same marker — not present in the base plugin itself)

Don't hand-bump these; version bumps come from Conventional Commits (`feat`/`fix`/`perf`) per [CONTRIBUTING.md](https://github.com/theme-my-login/.github/blob/main/CONTRIBUTING.md).

## Commands

```
npm install && composer install   # once, before anything else
npm run build                      # src/ -> build/
composer test                      # phpunit (loads src/ directly)
composer lint                      # phpcs, WordPress-Extra ruleset
composer lint:fix                  # phpcbf
```

## Tests

`composer test` runs the default suite (`tests/`, excluding `tests/multisite/`) against real WP core (via `roots/wordpress-no-content`/`wp-phpunit` dev dependencies) — no mocking, real `WP_UnitTestCase`. Needs a real DB (`WP_TESTS_DB_*` env vars, read by `tests/wp-tests-config.php`).

Coverage spans actions/forms/extensions (`test-action.php`, `test-actions.php`, `test-default-actions.php`, `test-form.php`, `test-form-field.php`, `test-forms.php`, `test-extensions.php`, `test-theme-my-login.php`), each built-in handler (`test-login-handler.php`, `test-logout-handler.php`, `test-lost-password-handler.php`, `test-password-reset-handler.php`, `test-registration-hooks.php`), the admin side (`test-admin-class.php`, `test-admin-functions.php`, `test-admin-extensions.php`, `test-ajax-handlers.php`, `test-settings.php`, `test-options.php`), and shared helpers (`test-functions.php`, `test-request-helpers.php`, `test-shortcode.php`, `test-url-filters.php`, `test-widget.php`).

Multisite (`tests/multisite/test-ms-functions.php`) runs separately via `tests/phpunit/multisite.xml` — see Multisite above.
