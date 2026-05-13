# Git Workflow Guide

This guide covers the full GitFlow process for contributing to the RMIT Learning Lab WordPress theme. We use **Sourcetree** to manage branches and **GitHub Actions** for automated deployment.

## Environment Overview

| Branch | Deploys To | When to Use |
|---|---|---|
| `main` | **PRD** — `prdlearninglab.wpenginepowered.com` | Standard target for all releases; PRD is the content source of truth |
| `develop` | **DEV** — `devlearninglab.wpenginepowered.com` | DEV content is frequently out of sync with PRD; reserved for large structural changes |
| `staging` | Staging (WP Engine) | Ad-hoc use |

> **Standard path is feature → `develop` → release → `main` (PRD).** For content-related changes the team will typically release straight to PRD without validating on DEV. DEV validation is optional and most useful for large structural changes. Local testing against a PRD database pull (`npm run site:pull:prod`) is the primary validation step. Syncing DEV up to PRD content requires upfront scoping — raise it with the team before scheduling.

## Branch Strategy

```
feature/my-thing
    ↓ GitFlow: Finish Feature
develop  →  DEV site (auto-deploys on merge)
    ↓ GitFlow: Start/Finish Release
main     →  PRD / LIVE site (standard release target)
    ↓
Tag + merge back into develop
```

Monitor all deployments at https://github.com/RMITLibrary/ll-wordpress-theme/actions.

## Everyday Workflow – Features

1. **Pull the latest PRD database before starting** so you're testing against real content:
   ```bash
   npm run site:pull:prod
   ```

2. **Start a new feature in Sourcetree**
   GitFlow ▶ Start Feature — name it `feature/my-feature-name`

3. **Do your work and commit** using [Conventional Commits](https://www.conventionalcommits.org/):
   ```
   feat: add new shortcode for resource cards
   fix: correct mobile overflow on topic pages
   style: update link colour in design system
   ```

4. **Push your branch**
   Right-click your feature branch in Sourcetree ▶ Push

5. **Finish the feature**
   GitFlow ▶ Finish Feature — merges into `develop` and optionally deletes the branch

6. **Push `develop`**
   Right-click `develop` in Sourcetree ▶ Push

   GitHub Actions deploys `develop` to DEV automatically. Monitor at https://github.com/RMITLibrary/ll-wordpress-theme/actions.

7. **Test locally** — primary validation is local against the PRD DB. Optionally check the DEV site for larger changes.

## Releasing to Production (LIVE)

Once satisfied with local testing:

1. **Start a release in Sourcetree**
   GitFlow ▶ Start Release — version name e.g. `2.034`

2. **Push the release branch**
   Right-click the release branch ▶ Push

3. **Finish the release**
   GitFlow ▶ Finish Release — merges into `main` and `develop`, tags `main` with the version, optionally deletes the release branch

4. **Push all changes and tags**
   Push `main`, `develop`, and tags in Sourcetree — right-click each branch ▶ Push, then push tags via Repository ▶ Push Tags

   GitHub Actions deploys `main` to PRD automatically. Monitor at https://github.com/RMITLibrary/ll-wordpress-theme/actions.

5. **Pull all latest changes locally** to stay in sync after the release.

## Hotfix Process

For urgent fixes needed on the live site:

1. **Start a hotfix in Sourcetree**
   GitFlow ▶ Start Hotfix — name it `hotfix/fix-broken-redirect`

2. **Commit your fix** following Conventional Commits:
   ```
   fix: correct redirect path on topic pages
   ```

3. **Finish the hotfix**
   GitFlow ▶ Finish Hotfix — merges into `main` and `develop`, creates a tag on `main`

4. **Push all changes and tags**
   Push `main`, `develop`, and tags in Sourcetree — right-click each branch ▶ Push, then push tags via Repository ▶ Push Tags

   PRD auto-deploys via GitHub Actions (https://github.com/RMITLibrary/ll-wordpress-theme/actions).

## Rollback

If a bad deployment reaches `main`:

- **Preferred:** use GitFlow ▶ Start Hotfix, revert the change, then Finish Hotfix.
- **Emergency reset** (check with the team first):
  ```bash
  git checkout main
  git revert <bad-commit-sha>
  git push origin main
  ```
  Avoid force-pushing to `main` unless absolutely necessary and no one else has pulled the broken change.

---

Questions or improvements? Reach out to the Digital Learning Team.
