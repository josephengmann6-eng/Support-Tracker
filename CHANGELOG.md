# Changelog

All build phases and changes for SupportTracker.

## [Logo Update] - 2026-10-19
- Replaced default logo with ST monogram redesigned in brand palette
- Applied to guest layout, app sidebar, and nav component
- White variant added for dark sidebar context

## [Visual Fixes - Batch 4] - 2026-10-18
- Restyled Account/Profile password form to match green brand theme (V1)
- Added themed green pagination component for Reports page (V2)
- Documented unapproved audit items in README Known Limitations

## [Mobile and Modal Fixes - Batch 3] - 2026-10-18
- Responsive stacked-card layout on Board below sm breakpoint (U1)
- Renamed vague "Open" link to "Update on board" on Handover page (U3)
- Streamlined Board modal: one title, one verb button, inline errors (U4)

## [Accessibility and Contrast Fixes - Batch 2] - 2026-10-18
- Replaced text-slate-400 with slate-500 for 4.5:1 contrast (A1)
- Fixed Done pill and flash message contrast to #166534 (A2)
- Fixed amber Pending button to dark amber text (A3)
- Added aria-current="page" to active sidebar item (A6)
- Added visible focus rings to sidebar links and date nav buttons (A6)

## [Bug Fixes - Batch 1] - 2026-10-18
- Fixed remark modal pre-filling previous remark (B1): modal now starts
  empty, previous remark shown as read-only context
- Fixed "Pending to Pending" showing when only a note was added (B2)
- Enabled adding notes to already-Done activities (B3)
- Added empty state to Dashboard Today's activities table (B7)

## [Phase 8] - Tests, README, Dockerfile - 2026-10-17
- 39 feature tests: auth, role access, audit trail, report filters
- Fresh-clone test fix: disabled Vite dependency in base TestCase
- README with setup steps, demo logins, design decisions, known limits
- Dockerfile written (unbuilt; Docker not installed on dev machine)

## [Phase 7] - Admin Pages - 2026-10-17
- Activities CRUD: create, edit, deactivate (never delete)
- Team management: create user, set role, activate/deactivate
- Admin-only middleware protecting /admin/* routes
- Member access returns HTTP 403

## [Phase 6] - Reports - 2026-10-17
- Date range filter with presets: Today, 7 days, 30 days, Custom
- Filter by user, activity, and status
- Summary count cards above paginated results table
- ReportFilters value object keeps controller thin

## [Phase 5] - Dashboard and Chart - 2026-10-17
- 4 stat cards: completion today, pending today, carried over, updates
- Chart.js 14-day completion trend bar chart
- Dark green hero Handover card with oldest 3 pending items
- Today's activities table with status, who, time, remark

## [Phase 4] - Activity Feed and Handover - 2026-10-17
- Feed: vertical timeline newest-first, time, person, staff ID,
  activity, status change pills, remark
- Handover: pending items from prior days grouped by age, days
  waiting, last remark, last person
- Overdue (2+ days) shown in red
- "Copy handover summary" button outputs plain text for email/chat
- "Update on board" link navigates to that date's board

## [Phase 3] - Daily Board and Update Service - 2026-10-17
- Board page with date picker and prev/next navigation
- Done and Pending buttons with remark modal per activity row
- Remark required when marking Pending
- ActivityUpdateService: DB transaction writes entry + entry_updates
- User name and staff ID snapshotted at write time

## [Phase 2] - Migrations, Models, Seeders - 2026-10-17
- users: staff_id, phone, department, role, is_active
- activities: title, description, is_active, created_by
- daily_entries: activity_id, date, status, remark, last_updated_by;
  unique(activity_id, date)
- entry_updates: append-only log with user snapshot fields;
  SQLite triggers block UPDATE and DELETE
- Indexes on date, status, user_id
- Seeders: 1 admin, 3 members, 5 activities, 7 days of history

## [Phase 1] - Foundation - 2026-10-17
- Laravel 12 (11 blocked by Composer security advisory)
- Breeze (Blade + Tailwind), SQLite, registration removed
- Fixed sidebar layout: dark forest green #0B2B1F, 20px rounded cards
- Design tokens: brand green #22C55E, amber #F59E0B, red #EF4444
- Inter font, responsive sidebar collapses to top bar on mobile
- User card at bottom with avatar initials, name, staff ID, logout
