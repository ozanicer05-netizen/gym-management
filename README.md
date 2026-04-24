# GymTrack (Rebuilt from ER Diagram)

GymTrack is a **PHP + MySQL** gym management project rebuilt from the ER diagram.

## What's Implemented

- Session-based authentication (`login`, `logout`, `me`)
- Protected backend APIs (unauthenticated requests return `401`)
- Pagination on list modules (members, trainers, classes, subscriptions, equipment, branches)
- Enhanced dashboard analytics (including inactive members, city distribution, top branches)
- Members CSV export/import

## Project Structure

- `frontend/` → UI pages
- `backend/` → API and repository layer
- `database/schema.sql` → schema (20 tables)
- `database/seed.sql` → sample seed data (English)

## Quick Start

1. Import `database/schema.sql` into MySQL (Workbench or CLI).
2. Copy `.env.example` to `.env` in the project root and fill your own credentials:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=gym_db
```

3. (Optional) Import `database/seed.sql` to load demo records.
4. Start the local PHP server:

```bash
cd /Users/ozan/Downloads
php -S localhost:8000
```

5. Open in browser:
   - `http://localhost:8000/gym/`

## Authentication

- Login page: `http://localhost:8000/gym/frontend/login.php`
- After login, session is used for frontend pages and protected APIs.

### Seeded demo users

- Admin: `ozan.admin@fitsphere.local` / `Admin123!`
- Manager: `manager.ankara.cankaya@fitsphere.local` / `Manager123!`
- Admin: `sevval.admin@fitsphere.local` / `Sevval123!`
- Admin: `beren.admin@fitsphere.local` / `Beren123!`
- Admin: `nisa.admin@fitsphere.local` / `Nisa123!`
- Admin: `nehir.admin@fitsphere.local` / `Nehir123!`

All roles (admin, developer, manager) use the same login page:

- `http://localhost:8000/gym/frontend/login.php`

If you import `database/seed.sql`, these accounts are created with valid bcrypt hashes.

## Pagination

The following pages now use server-side pagination with page metadata:

- `frontend/members.php`
- `frontend/trainers.php`
- `frontend/classes.php`
- `frontend/subscriptions.php`
- `frontend/equipment.php`
- `frontend/branches.php`

API responses include:

- `meta.total`
- `meta.page`
- `meta.limit`
- `meta.totalPages`
- `meta.hasPrev`
- `meta.hasNext`

## Seed Data

`database/seed.sql` adds realistic demo records for all core modules:

- users, roles, user-role assignments
- branches, members, subscriptions, payments
- trainers, skills, classes, schedules, reservations
- equipment, maintenance, attendance, notifications, feedback

After importing seed data, dashboard cards and list pages show non-zero values.

## Members CSV Export / Import

### Export

- Endpoint: `backend/api/members_export.php`
- Requires authenticated session.
- Returns `members_export.csv`.

### Import

- Endpoint: `backend/api/members_import.php` (POST multipart form-data)
- Requires authenticated session.
- Available in `frontend/members.php` via import button.

Expected CSV columns (header optional):

1. `name`
2. `surname`
3. `email`
4. `phone`
5. `branch_name`
6. `status` (`active`, `inactive`, `suspended`)

Notes:

- Rows with missing `name`, `surname`, or `email` are skipped.
- Duplicate emails are skipped.
- Unknown branch names fallback to branch id `1`.
- Imported users get default password: `Member123!`.

## Team Collaboration

### First-time setup (for collaborators)

```bash
git clone https://github.com/ozanicer05-netizen/gym-management.git
cd gym-management
cp .env.example .env
```

Then each teammate updates `.env` with local MySQL credentials.

### Getting latest updates

```bash
git checkout main
git pull origin main
```

If someone is working on a feature branch:

```bash
git checkout <feature-branch>
git pull origin <feature-branch>
```

## Secret Safety

- Keep real passwords only in `.env`.
- `.env` is already ignored by `.gitignore`.
- Commit `.env.example`, never commit `.env`.

If `.env` was accidentally tracked before:

```bash
git rm --cached .env
git commit -m "Stop tracking .env"
git push
```
