# Contributing

## Commits and PR titles

This repo uses [Conventional Commits](https://www.conventionalcommits.org/). PRs merge into `master` as real merge commits, not squashed, so every individual commit lands on `master` verbatim and is what release automation actually scans — the PR title is checked too (CI lints both), but it's the commits that count.

Format: `type: subject` (lowercase after the colon, no trailing period). This applies per-commit: squash together commits that are really one change split across saves before opening a PR (see the note on squashing below), but every commit that survives still needs its own correct type — CI lints each one individually, not just the PR title.

Allowed types:

| Type | Use for |
|---|---|
| `feat` | User-facing functionality |
| `fix` | User-facing bug fix |
| `chore` | Dev tooling, dependency bumps, anything with no user-facing effect |
| `ci` | CI/workflow changes |
| `docs` | Documentation only |
| `build` | Build process/tooling |
| `perf` | Performance improvement |
| `refactor` | Code change with no behavior change |
| `test` | Test-only changes |

`feat`/`fix`/`perf` are the only types that ship in the plugin's changelog and trigger a version bump. The rest are invisible to plugin users by design.

**Branch names should be prefixed with the same type**, e.g. `fix/nonce-check-on-license-page`, `feat/passwordless-login`, `chore/bump-phpunit`.

### Squashing

Before opening a PR, squash commits that are really incremental edits to the same change (fixup-style saves) into one. Leave commits separate when a PR genuinely contains multiple distinct logical changes — that's a per-PR judgment call, not something CI enforces or a repo-wide setting forces.

### Major version bumps

A major bump isn't manual — it's the same commit-driven mechanism as `feat`/`fix`, just marked as breaking. Either:

- Add `!` after the type/scope: `feat!: drop support for PHP 7.4`
- Or add a `BREAKING CHANGE:` footer to the commit body (any type):

```
fix!: remove deprecated tml_get_login_url() in favor of tml_get_action_url()

BREAKING CHANGE: tml_get_login_url() has been removed; use tml_get_action_url( 'login' ) instead.
```

To force a specific version regardless of what the commits would otherwise compute (e.g. a deliberate version jump), add a `Release-As: X.Y.Z` footer to any commit.

### Keep subjects technical; use `Release-Note:` for user-facing wording

Commit/PR subjects should describe the actual change precisely, the way anyone reading `git log` would want — not marketing copy:

- `fix: avoid double-firing login_redirect during password reset` — not `fix: prevent site crash on Bluehost`
- `fix: add current_user_can check to admin-post handler` — not `fix: check permission when managing license keys`

If a change should say something different to plugin users than the subject does, add a `Release-Note:` trailer in the commit/PR body:

```
fix: avoid double-firing login_redirect during password reset

Release-Note: Prevent site crash on Bluehost during password reset.
```

The changelog draft script uses the `Release-Note:` line when present and falls back to the subject when it isn't, so it's optional, not required on every commit.

If a change is purely internal (refactor, test coverage, tooling) it should be `chore`/`refactor`/`test`, not a reworded `fix`.
