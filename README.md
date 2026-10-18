# SupportTracker

An activity tracking system with shift handover for application support teams. Each day the team works through a fixed list of checks (SMS counts, failed transaction queue, overnight batches, disk usage, backups). SupportTracker records who did what and when, keeps anything unfinished visible, and produces a copy-paste handover summary for the next shift.

Stack: **Laravel 12** (see note below), Laravel Breeze (Blade + Tailwind CSS 3), SQLite, Chart.js, Alpine.js, Pest. Timezone is `Africa/Accra`.

> **Why Laravel 12 and not 11?** The brief asked for Laravel 11, but every Laravel 11 release is now blocked by Composer's security-advisory check (11 is end-of-life). I used Laravel 12 instead of disabling that check. Nothing in the app depends on a 12-only feature.

## Setup

Requirements: PHP 8.2+ (with `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`), Composer 2, Node 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # Windows PowerShell: New-Item database/database.sqlite
php artisan migrate --seed
npm install
npm run build                           # or `npm run dev` while developing
php artisan serve
```

Open <http://127.0.0.1:8000>. To start over with fresh demo data: `php artisan migrate:fresh --seed`.

### Demo logins

All demo accounts use the password **`password`**. Change them before any real use.

| Role   | Name               | Staff ID | Email                              |
|--------|--------------------|----------|------------------------------------|
| Admin  | Ama Serwaa Boateng | STF-0001 | `admin@supporttracker.test`        |
| Member | Kofi Owusu         | STF-1042 | `kofi.owusu@supporttracker.test`   |
| Member | Efua Quaye         | STF-1057 | `efua.quaye@supporttracker.test`   |
| Member | Yaw Darko          | STF-1063 | `yaw.darko@supporttracker.test`    |

The seeders also create the five activities and a week of past entries and updates. Three items are deliberately left pending (1, 3 and 5 days old) so the Handover page and the dashboard hero card are not empty.

### Tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database (configured in `phpunit.xml`), never your real data.

### Docker

```bash
docker build -t supporttracker .
docker run --rm -p 8000:8000 -e SEED_DEMO_DATA=true -e APP_KEY=base64:$(openssl rand -base64 32) supporttracker
```

The container builds the assets, migrates on start, and seeds demo data only when `SEED_DEMO_DATA=true`. It serves with `php artisan serve`, which is fine for a demo but not for production traffic. Mount `/var/www/html/database` as a volume if you want the SQLite file to survive restarts. **The Dockerfile has not been built or run** (Docker was not available where this was developed), so treat it as untested.

## Pages

| Page | URL | Who | What it does |
|------|-----|-----|--------------|
| Dashboard | `/dashboard` | all | Four stat cards, 14-day completion chart, handover hero card, today's table |
| Daily Board | `/board?date=` | all | Table of active activities for a day with Done / Pending buttons and a remark modal |
| Activity Feed | `/feed?date=` | all | Timeline of every status change that day, newest first |
| Handover | `/handover` | all | Pending items from earlier days, grouped by age, plus "Copy handover summary" |
| Reports | `/reports` | all | Filterable, paginated audit log with summary counts |
| Activities | `/admin/activities` | admin | Create, edit, activate / deactivate |
| Team | `/admin/users` | admin | Create users, edit, activate / deactivate |

## Design decisions

**Append-only history.** `entry_updates` is the source of truth for "what happened". Every status change inserts a row and updates the `daily_entries` row in one database transaction (`ActivityUpdateService::update`). Rows are never updated or deleted, and this is enforced three ways: the model throws on update/delete, SQLite triggers abort `UPDATE` and `DELETE`, and no route or controller touches them. Timestamps are set on the server; the request cannot supply one.

**Snapshots.** Each log row stores `user_name` and `user_staff_id` as they were at the time. Renaming a person or changing their staff ID later does not rewrite history (covered by a test).

**Lazy daily entries, no cron.** Viewing a date calls `firstOrCreate` for each active activity. Two guards stop this from inventing work: nothing is created for future dates, and no entry is created for a day before the activity existed. Without the second guard, browsing last year would fill the Handover page with phantom pending items. A side effect to be aware of: an entry only exists for a day someone opened (or the seeder created), so a day nobody looked at has no row and is not counted as pending.

**What "pending" and "overdue" mean.** Each day has its own row per activity, so an item left pending yesterday stays on yesterday's row and today gets a fresh one. The Handover page lists pending rows from days before today. A row is *overdue* (red) once it has been pending for 2 or more days; one day old is amber.

**Activities and users are deactivated, never deleted.** There are no delete routes (a test asserts `DELETE` returns 405) and the foreign keys use `RESTRICT`. Deactivated activities disappear from the board but stay in reports. An admin cannot deactivate or demote themselves, so the system cannot be left without an admin.

**Authorization.** Admin routes sit behind an `admin` middleware, and the form requests re-check `isAdmin()`. Members can only update statuses. Updates to deactivated activities are rejected.

**Authentication.** Registration, email verification and password-reset routes are removed; accounts come from seeders or an admin. Login is throttled (Breeze's 5 attempts per email and IP). `is_active` is part of the login credentials, so inactive users cannot sign in, and an `active` middleware signs out anyone deactivated mid-session. CSRF protection is Laravel's default. Profile editing is limited to changing your own password.

**Controllers stay thin.** Logic lives in services and support classes: `ActivityUpdateService` (lazy entries, status changes), `HandoverService` (pending items, grouping, the plain-text summary), `DashboardStats`, and `ReportFilters` (parses and applies report filters, so it is unit-testable).

**Indexes.** `daily_entries`: unique `(activity_id, date)`, plus `date`, `status`, `(status, date)` and `last_updated_by`. `entry_updates`: `user_id`, `new_status`, `created_at` and `(daily_entry_id, created_at)`. `users`: `role`, `is_active`, unique `staff_id`.

**Design system.** Tokens live in `tailwind.config.js` (forest `#0B2B1F`, hero `#0F3D2B`, canvas `#EEF3EF`, accent `#22C55E`, amber `#F59E0B`, red `#EF4444`, 20px card radius, Inter). Shared classes (`.card`, `.pill-*`, `.btn-*`, `.table-clean`) are in `resources/css/app.css`. The sidebar collapses to a top bar with a slide-in menu below the `lg` breakpoint.

## Known limitations

- `users.staff_id` is nullable at the database level because SQLite cannot add a `NOT NULL` column without a default to an existing table. The application always requires it.
- Timestamps are shown in `Africa/Accra` (GMT, no daylight saving), so they equal UTC. If you change `APP_TIMEZONE`, stored values are in the application timezone, not UTC.
- No password reset by email: an admin sets a new password from the Team page.
- Reports have no CSV export.
- **Audit items not fixed / deferred:**
  - **B4–B6 (Bugs / Operational behavior):**
    - Unopened days are not retroactively tracked or flagged as overdue (daily entries are lazily generated only when a date is visited or seeded).
    - No real-time updates / WebSockets on the Daily Board or Feed; updates made by team members require a manual page reload.
    - Shifts spanning midnight are divided across two calendar dates rather than grouped by operational shift windows.
  - **A4–A5 (Accessibility & contrast):**
    - The 14-day completion trend chart (Chart.js canvas) provides visual trend data but lacks an accessible fallback data table or text description for screen readers.
    - Status modal focus trapping: the Board remark modal closes on Escape and click-away, but does not strictly trap tab focus in a loop or restore focus to the triggering button.
  - **U2 (Mobile responsiveness):**
    - The 14-day completion trend chart on the Dashboard and the Admin management tables (Users, Activities) rely on horizontal scrolling on small screens (`<sm`) rather than card-stacked views.
  - **V3+ (Visual consistency):**
    - Admin CRUD management pages use default clean table layouts and do not yet use the custom green pagination view introduced on Reports.

