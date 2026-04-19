# GymTrack (Rebuilt from ER Diagram)

GymTrack is a **PHP + MySQL** gym management project rebuilt from the ER diagram.

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

## Seed Data

`database/seed.sql` adds realistic demo records for all core modules:

- users, roles, user-role assignments
- branches, members, subscriptions, payments
- trainers, skills, classes, schedules, reservations
- equipment, maintenance, attendance, notifications, feedback

After importing seed data, dashboard cards and list pages show non-zero values.

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
