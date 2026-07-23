# North East Coarse Fishing

Community portal for anglers across Durham, Tyne & Wear, Northumberland and Teesside — discover venues/complexes, browse an interactive map, log sessions, and publish official fishery updates.

## Stack

- Laravel 13 (Laravel 11 was blocked by Packagist security advisories)
- Blade + Alpine.js + Tailwind CSS (Breeze)
- Livewire 3 (via Filament)
- Spatie Laravel Permission (roles: `angler`, `fishery_manager`, `super_admin`)
- Filament 4 admin panel at `/admin`
- Leaflet.js maps (OpenStreetMap tiles)

## Setup

```bash
cd /Users/chrismitchell/PhpstormProjects/ne-coarse-fishing
composer install
cp .env.example .env   # if needed
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run build
php artisan serve --port=8001
```

Open [http://127.0.0.1:8001](http://127.0.0.1:8001).

### Demo accounts (password: `password`)

| Email | Role |
|-------|------|
| `admin@nefishing.test` | Super Admin (Filament) |
| `manager@nefishing.test` | Fishery Manager |
| `angler@nefishing.test` | Angler |

Admin panel: [http://127.0.0.1:8001/admin](http://127.0.0.1:8001/admin)

Email verification is enabled (`MustVerifyEmail`). In local, verification links are written to `storage/logs/laravel.log` (`MAIL_MAILER=log`).

## Features

- **Venue directory** with complexes vs single waters, species, tickets, tactics
- **Leaflet map** filtered by species & ticket type
- **Venue submission** with pin-drop coordinates and dynamic child waters
- **Session logger** with catches + mobile photo uploads
- **Manager tools** for match reports, stocking updates, announcements
- **Ownership claims** moderated in Filament
- **Admin moderation** for venue approval, users, claims, sessions

## MySQL / PostgreSQL

Update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ne_coarse_fishing
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate:fresh --seed`.
