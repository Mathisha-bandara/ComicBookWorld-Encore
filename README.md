# Comic Book World — V1.0 Release Candidate

A PHP and MySQL web application for browsing and purchasing physical comic books, with a customer storefront and an administrative management panel. This is a portfolio / local-development project built with WAMP.

> **Release status:** V1 release candidate. Run the checklist in `GITHUB_RELEASE_CHECKLIST.md` before tagging `v1.0`. The project has been tested incrementally in the author's local WAMP environment; a clean installation should still be verified before calling the release final.

## Features

### Customer storefront
- Registration, login and logout
- Product catalogue and product details
- Shopping cart and checkout/order placement
- Order tracking and purchase history
- Customer profile
- Promotion-code support
- FAQ, Contact Us, About Us and Terms & Conditions pages

### Administration
- Dashboard, analytics and reports
- Product and order management
- Customer management
- Promotions and FAQ management
- Administrative activity log
- Five built-in administrator roles: Super Admin, Secondary Admin, Store Manager, Order Staff and Inventory Staff
- Role/permission assignment
- Administrator access periods and weekly schedules
- Account suspension and individual/global administrative freeze controls

### Security mechanisms
- Session-based authentication
- Password hashing and verification
- Prepared statements in key database operations
- Role-based permission checks
- CSRF token checks on protected forms
- Server-side access-period, schedule and freeze checks

These features are intended for demonstration and learning. This repository is not a security-audited production service, and no payment gateway is included.

## Technology stack

- PHP 8.x
- MySQL / MariaDB
- HTML5, CSS3 and JavaScript
- Bootstrap 5 (admin interface)
- WAMP Server for local development

## Local installation (Windows + WAMP)

1. Install WAMP and start **Apache** and **MySQL/MariaDB**. Confirm the WAMP services are running.
2. Copy the project into `C:\wamp64\www\ComicBookWorld-Portfolio`.
3. In phpMyAdmin, run the repository's root `database.sql` in a new/empty local database. It creates the current schema without `DROP TABLE` statements.
4. Run `database/seed_demo.sql` to add the five built-in roles, permissions, example comic products, starter FAQs and demonstration promotion codes. **Use this seed only for a fresh/demo database.** It does not add any users, passwords, customer records, orders or activity-log entries.
5. Copy `config.local.example.php` to `config.local.php`. Edit the values if your local MySQL setup differs. `config.local.php` is ignored by Git and should not be committed.
6. Open `http://localhost/ComicBookWorld-Portfolio/HomePage.php`.
7. Register a normal account through the site. To make that account the initial Super Admin in a fresh local demo database, replace the email placeholder below with the registered email and run it once in phpMyAdmin:

   ```sql
   UPDATE users u
   JOIN admin_roles r ON r.name = 'Super Admin'
   SET u.role = 'admin',
       u.admin_role_id = r.id,
       u.account_status = 'active'
   WHERE u.email = 'REPLACE_WITH_YOUR_REGISTERED_EMAIL';
   ```

8. Log out and log back in, then open `http://localhost/ComicBookWorld-Portfolio/admin/AdminPanel.php`.

There is intentionally **no default Super Admin username or password** in this repository. Create your own local demo account. Never use this promotion query on a public production database.

## Database files

- `database.sql` — non-destructive schema creation for a fresh local database. It has no `DROP TABLE` statements and no account/customer/order records.
- `database/seed_demo.sql` — role/permission definitions and non-personal demo catalogue data; run on a fresh/demo database only.
- `database/migrations/20261014_create_admin_individual_freezes.sql` — migration for existing local databases that are missing the individual-freeze table. Only run it if the table is absent.

The live development database can contain changes that are not represented in a Git-tracked schema file until those changes are deliberately added to the setup script. Back up your working database before any schema migration. Do not upload a raw phpMyAdmin export of your live database: it may contain user details, order data or password hashes.

## Local database configuration

The connection settings are read from `config.local.php` when that file exists; otherwise the application uses WAMP-friendly local defaults. The example file contains only local-development placeholders. A deployed environment must use its own least-privilege database user and appropriate secrets management; do not run a public deployment with the WAMP `root` account.

## V1 smoke-test checklist

- [ ] Homepage loads and product catalogue displays demo data.
- [ ] Registration and login work; password is stored as a hash.
- [ ] Suspended account cannot log in; reactivated account can.
- [ ] Cart, checkout and order history work against a fresh setup.
- [ ] Super Admin can manage products, orders, FAQ and promotions.
- [ ] All five built-in roles appear as system roles.
- [ ] Role permissions restrict pages/actions as intended.
- [ ] Access expiry and weekly schedules behave correctly.
- [ ] Individual freeze and unfreeze behave correctly.
- [ ] Global freeze blocks non-Super-Admin changes while Super Admin can recover/unfreeze.
- [ ] Project installs from this README and the committed SQL files on a clean database.

## Portfolio screenshots

Add screenshots to a folder such as `docs/screenshots/` before creating the GitHub release. Avoid screenshots containing personal email addresses, session details, private customer data or passwords.

## Versioning plan

- **V1.0:** stable customer store and core administration; README, clean setup scripts and verified smoke tests.
- **V2.0:** deeper security and administrator-management improvements after V1 is tagged.
- **V3.0:** further analytics and customer-experience improvements.
- **V4.0:** architecture, deployment and documentation improvements.

## License

A license has not been selected yet. Choose a license before presenting the repository as open source. Only publish image assets you have permission to distribute; otherwise replace them with assets you own or are licensed to use.
