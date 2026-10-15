# GitHub V1 release checklist

Use this checklist against the actual working folder, not only against an older downloaded ZIP.

## Before staging
- [ ] Make a copy of the working project folder.
- [ ] Export a backup of the current database and keep it outside the Git repository.
- [ ] Copy the tested versions of the recent admin-management/freeze changes into the working folder if they are newer than the source archive.
- [ ] Replace the old root `database.sql` with the curated non-destructive version from this pack.
- [ ] Add `database/seed_demo.sql`, migration, config example and the updated README.
- [ ] Copy `config.local.example.php` to local `config.local.php`; keep `config.local.php` untracked.
- [ ] Remove the unused `.env.example` if the application is not configured to parse `.env` files.

## Secrets and data review
- [ ] Confirm the Git staging area does not include `config.local.php`, a live phpMyAdmin export, real customer/order data, passwords, password hashes or session logs.
- [ ] Check that no database dump includes the legacy `admins` table with the old demo password.
- [ ] Review image licenses or replace assets that are not yours to redistribute.
- [ ] Confirm the repository contains no downloaded ZIPs or editor/temp files.

## Functional checks
- [ ] Fresh install using README on a new empty database.
- [ ] Registration/login/logout.
- [ ] Product browsing, cart and checkout/order placement.
- [ ] Product/order/FAQ/promotion CRUD as Super Admin.
- [ ] Admin roles and permissions for all five roles.
- [ ] Account suspension stops login.
- [ ] Individual freeze blocks admin modifications for the selected non-Super-Admin.
- [ ] Global freeze blocks all non-Super-Admin modifications; Super Admin can unfreeze.
- [ ] Access expiry and weekly schedule work using Monday=1 through Sunday=7.
- [ ] Test responsive layout using browser DevTools.
- [ ] Confirm Apache/PHP error output does not expose SQL, paths or stack traces to normal users.

## GitHub publication
- [ ] Review `git status` before staging.
- [ ] Stage only intended project files.
- [ ] Create a descriptive commit, e.g. `Prepare Comic Book World V1 release`.
- [ ] Push the repository.
- [ ] Add screenshots and create release tag `v1.0` only after the checklist is complete.
