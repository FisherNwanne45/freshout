# Remote Build Migration Guide

This repository includes an idempotent migration runner at:

- `user/admin/migrate.php`

Use it after uploading this codebase to an older system/database.

## What It Does

The migration runner is designed for legacy databases (for example old dumps like `fisherbk`) and applies missing structure/settings for the newer codebase.

### 1) Schema alignment (idempotent)

It ensures key newer tables exist:

- `site_settings`
- `currencies`
- `account_types`
- `account_balances`
- `exchange_rates`
- `loan_applications`
- `card_requests`
- `cards`
- `term_deposits`
- `investment_accounts`
- `investment_positions`
- `robo_profiles`
- `product_activity`

It also executes built-in `user/partials/auto-migrate.php` steps (v2-v14) for additional incremental changes.

### 2) Account column upgrade (`mname` -> `pin`)

Migration logic:

- If `account.mname` exists and `account.pin` does not exist: it renames `mname` to `pin`.
- If both exist, it backfills `pin` from `mname` where `pin` is empty.

This preserves existing PIN values from legacy data.

### 3) Hardcoded admin settings push

`migrate.php` contains a hardcoded `$hardcodedSiteSettings` payload and applies it into `site_settings`.

- SMTP keys are intentionally excluded.
- Existing records are preserved and updated via upsert (`ON DUPLICATE KEY UPDATE`).
- Script is safe to run repeatedly.

### 4) Site profile push

`migrate.php` also contains `$hardcodedSiteRow` for non-user site profile fields and updates the first row in `site` if present.

## Important Exclusions

Per requirement, migration does **not** transfer:

- User account credentials/data migration from another system source
- SMTP settings (`smtp_*` keys)

## How to Run

### Option A: Admin button

1. Login to admin
2. Open `user/admin/settings.php`
3. Click **Open Build Migration**
4. Click **Run Build Migration**

### Option B: Direct URL

- Visit: `user/admin/migrate.php?run=1`

## Progress Logs

`migrate.php` prints step-by-step logs in the UI:

- `[OK]` completed
- `[SKIP]` already satisfied or not applicable
- `[ERROR]` failed step with database error detail
- `[DONE]` final status

## Before Deploying to Another System

Update hardcoded values in `user/admin/migrate.php`:

- `$hardcodedSiteSettings`
- `$hardcodedSiteRow`

These should match the exact source admin settings you want replicated across remote systems.

## Schema Comparison Pointers (Legacy vs New)

Legacy systems typically have:

- `site` table settings
- no `site_settings` key/value table
- `account.mname` without `account.pin`
- no multi-currency/account products tables

Newer system expects:

- `site_settings` for most admin controls
- `pin` workflow compatibility
- currency/account/product tables used by newer pages and features
- incremental migration flags (`db_migration_v*`)
