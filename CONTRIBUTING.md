# Contributing

## Commits and PR titles

This repo uses [Conventional Commits](https://www.conventionalcommits.org/). PR titles are what matters most in practice — GitHub's default squash-merge uses the PR title as the resulting commit message on `master`, and that's what release automation will key off of.

Format: `type: subject` (lowercase after the colon, no trailing period).

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
