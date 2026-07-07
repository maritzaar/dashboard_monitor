# Monitoring Alat Berat

Aplikasi monitoring alat berat berbasis web menggunakan Laravel. Digunakan untuk memantau data operasional, fuel usage, dan performa aset.

## Fitur

- **Dashboard Overview** — Ringkasan data operasional (working hours, idle, fuel usage)
- **Laporan** — Laporan detail per aset dengan filter area, group, IO group
- **Import Data** — Upload file Excel (Fleet Utilization & Fuel)
- **Export Data** — Export data ke Excel
- **Multi-user** — Admin & User dengan role-based access
- **Multi-bahasa** — Dukungan bahasa Indonesia & English

## Tech Stack

- **Backend**: PHP 8.4, Laravel 13
- **Database**: MySQL
- **Frontend**: Blade, Vite, Chart.js
- **Import/Export**: Maatwebsite Excel

## Setup

```bash
# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Start dev server
php artisan serve
npm run dev
```

## Environment

Pastikan MySQL sudah berjalan dan database `monitoring_alat` sudah dibuat.
