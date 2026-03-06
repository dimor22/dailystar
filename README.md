# DailyStars

DailyStars is a Laravel 10 + Livewire kids task manager with gamification (points, stars, streaks), kid PIN login, and a parent dashboard.

## Tech Stack

- Laravel 10
- Livewire 3
- TailwindCSS (default v3 config)
- Alpine.js

## Local Setup

1. Install backend/frontend dependencies:

```bash
php ./composer install
npm install
```

2. Prepare environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Use SQLite for quick local start:

```bash
touch database/database.sqlite
```

Then set in `.env`:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/project/database/database.sqlite
```

4. Run migrations and seed data:

```bash
php artisan migrate:fresh --seed
```

5. Start the app:

```bash
php artisan serve
npm run dev
```

## Seeded Accounts

- Parent login: `/parent/login`
	- Email: `parent@dailystars.app`
	- Password: `password`
- Kid login: `/login`
	- PINs are seeded per kid in `DatabaseSeeder`.

## Main Routes

- `/login` → Kid avatar + PIN login
- `/parent/login` → Parent sign in
- `/dashboard` → Parent dashboard (protected by `parent.auth` middleware)
- `/api/complete-task` → Task completion endpoint used by task cards

## Quick Checks

```bash
php artisan route:list
php artisan test
npm run build
```
