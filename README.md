# IoT Fleet Telematics & Heavy Asset Tracking Dashboard

A full-stack, enterprise-grade Fleet Telematics and IoT Heavy Machinery Monitoring Dashboard engineered with **Laravel 13 (PHP 8.4)**, **Vue 3**, **Inertia.js**, **Tailwind CSS**, and **ApexCharts**.

---

## 📋 Table of Contents
- [Prerequisites](#-prerequisites)
- [Installation & Setup](#-installation--setup)
- [Application Architecture & Prefix](#-application-architecture--prefix)
- [Feature Highlights](#-feature-highlights)
  - [1. Fleet Telematics Analytics Dashboard (Image 1 Requirement)](#1-fleet-telematics-analytics-dashboard-image-1-requirement)
  - [2. Day Overview Gantt Timeline & Filters (Image 2 Requirement)](#2-day-overview-gantt-timeline--filters-image-2-requirement)
  - [3. Heavy Asset Inventory Management (`/assets`)](#3-heavy-asset-inventory-management-assets)
  - [4. High-Throughput IoT Ingestion Pipeline (`/telematics/ping`)](#4-high-throughput-iot-ingestion-pipeline-telematicsping)
  - [5. Interactive Telemetry Simulator & Gateway](#5-interactive-telemetry-simulator--gateway)
- [Database Schema & Performance Optimizations](#-database-schema--performance-optimizations)
- [Automated Testing](#-automated-testing)
- [Scheduled Cron Jobs](#-scheduled-cron-jobs)
- [Key Architectural & Domain Assumptions](#-key-architectural--domain-assumptions)

---

## 🛠️ Prerequisites

Ensure you have the following installed on your host system:
- **Docker** & **Docker Compose** (recommended setup via Laravel Sail)
- Alternatively for local native setup:
  - **PHP >= 8.4** with extensions (`pdo_mysql`, `bcmath`, `curl`, `mbstring`, `openssl`, `tokenizer`, `xml`)
  - **Composer 2.x**
  - **Node.js >= 20.x** & **npm**
  - **MySQL 8.x**

---

## 🚀 Installation & Setup

### 1. Clone & Environment Configuration
```bash
git clone <repository_url>
cd iot_dashboard

# Copy environment file
cp .env.example .env
```

Ensure your `.env` contains your preferred application name, database connection, and port configuration:
```env
APP_NAME="Telematics Fleet"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_PORT=8010

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
FORWARD_DB_PORT=3307

ADMIN_EMAIL=admin@example.com
```

### 2. Start Application Containers (Laravel Sail)
```bash
./vendor/bin/sail up -d
# or using docker compose directly:
docker compose up -d
```

### 3. Application Key & Database Setup
```bash
# Generate application encryption key
docker compose exec -u sail laravel.test php artisan key:generate

# Run database migrations and seed realistic assessment data
docker compose exec -u sail laravel.test php artisan migrate:fresh --seed
```

### 4. Build Frontend Assets
Build production-optimized JavaScript & CSS bundles:
```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd)":/var/www/html -w /var/www/html node:24-alpine npm run build
```
*(Or run `npm run dev` in your local environment for hot-module reloading).*

### 5. Access the Dashboard
Open your browser and navigate to:
**`http://localhost:8010`**

---

## 🌐 Application Architecture & Prefix

| Prefix / Route | HTTP Verb | Controller & Method | Description |
| :--- | :---: | :--- | :--- |
| **`/`**, **`/dashboard`** | `GET` | `DashboardController@showFleetOverview` | Main Telematics Fleet Dashboard (Image 1 & Image 2) |
| **`/dashboard/day-overview`** | `GET` | `DashboardController@dayOverview` | Partial Inertia / JSON reload for Gantt schedule & filters |
| **`/dashboard/metrics`** | `GET` | `DashboardController@metrics` | Partial Inertia / JSON refresh for 4 KPI analytics cards |
| **`/assets`** | `GET/POST` | `AssetController@index`, `@store` | Heavy machinery fleet inventory directory & registration |
| **`/assets/{asset}`** | `PUT/DELETE` | `AssetController@update`, `@destroy` | Asset modification and cascading decommission |
| **`/telematics/ping`** | `POST` | `AssetActivityIntervalController@ping` | High-throughput CAN-bus hardware telemetry ingestion endpoint |
| **`/activity-intervals`** | `CRUD` | `AssetActivityIntervalController` | Telematics activity interval resource routes |

---

## ✨ Feature Highlights

### 1. Fleet Telematics Analytics Dashboard (Image 1 Requirement)
A 2x2 responsive analytics grid visualizing historical fleet operations over a 15-day reporting cycle:
- **Traveled Distance (`431km`)**: Comparative area chart comparing *Current Period* (solid amber fill with `#C25E1A` line) against *Previous Period* (dashed `#CCA380` line). Alternate day labels are computed dynamically.
- **Engine Hours**: Stacked bar chart showing machinery utilization. Bottom bars represent active working hours (`#00875A` emerald) and top stacked bars represent idle hours (`#E07A28` amber).
- **Activity Breakdown (`1,246 Hours`)**: Donut chart with total hours centered, mapping fleet operational states:
  - `Ignition Off` (`#881337`)
  - `Ignition / Active` (`#00875A`)
  - `Idling` (`#E07A28`)
- **Messages Received**: Bar chart visualizing total telemetry packets received across the fleet per reporting date.
- **Card Icons**: Uses Google Font `route` and Material Design Icons (`mdiEngine`, `mdi-car`, `mdi-comment-processing`).

### 2. Day Overview Gantt Timeline & Filters (Image 2 Requirement)
A timeline tracking heavy machinery duty-cycles over the operating shift window (`11:00` - `18:00`):
- **Duty-Cycle Operational Badges**: Dynamically calculated percentage badges per machine:
- **Availability / Utilization Ratio (Sky Blue Badge)**: `(Total Running Minutes / Shift Window Minutes) * 100` (where running = active + idle minutes).
- **Idling Ratio (Orange Badge)**: `(Idle Minutes / Running Minutes) * 100`. Only displayed if the machine spent time idling.
- **Active Working Ratio (Teal Badge)**: `(Active Minutes / Running Minutes) * 100`. Only displayed if the machine worked during the shift.
  - 🟫 **Auxiliary / Attachment Ratio**: Specialized implement duty cycles.
- **Timeline Ribbon Strips**: Color-coded horizontal timeline bars clamped to time boundaries:
  - Teal (`#0D9488`): Active working
  - Orange (`#EA580C`): Idling
  - Dark Brown (`#78350F`): Ignition off
- **Interactive Collapsible Filter**:
  - Filter button styled to match the requirement screenshot.
  - Timeline Date picker.
  - Machinery Category dropdown (`Loader`, `Wheel Loader`, `Bulldozer`, `Excavator`).
  - Search input for real-time asset code or name filtering.

### 3. Heavy Asset Inventory Management (`/assets`)
- Complete CRUD interface for registering, updating, and decommissioning machines.
- **Live Status Detection**: Checks latest telemetry timestamp; if a machine has not emitted a ping within 24 hours, it is flagged as **Offline**.
- Cascading deletion of activity records upon machine decommissioning.

### 4. High-Throughput IoT Ingestion Pipeline (`/telematics/ping`)
- Accepts telemetry pings (`asset_id`, `status`, optional `timestamp`).
- **Heartbeat Management**: Extends ongoing intervals during continuous operation without creating duplicate database rows.
- **State-Machine Transitions**: Finalizes ongoing intervals and opens new intervals on operational state changes.
- **Midnight Rollover Splitting**: Splits intervals at `00:00:00` according to the machine's local deployment timezone (`Asia/Dubai`).
- **Validation Anomaly Queuing & Deduplication**:
  - Invalid packets trigger a critical audit log entry and return HTTP 422.
  - An email alert is queued to the admin on queue `notifications`.
  - Subsequent duplicate anomalies from the same device within 60 minutes are automatically throttled to prevent email flooding.

### 5. Interactive Telemetry Simulator & Gateway
Integrated into the top navigation bar via the **IoT Simulator** button:
- Send sample pings (`active`, `idling`, `ignition_off`) for any registered machine.
- Set custom historical timestamps to simulate timeline slices or test offline triggers.
- **Test Anomaly Ping**: Dispatches corrupt payloads to verify validation error handling, demonstrating email alert queueing on the first failure and 60-minute duplicate throttling on repeat clicks.
- **Real-Time Navbar Status**: Shows the most recent telemetry ping across the fleet (`Last Ping: [Date/Time] [Asset Code]`).

---

## 🗄️ Database Schema & Performance Optimizations

### Composite Indexing
- `asset_activity_intervals`:
  - `idx_asset_local_date` on `(asset_id, local_date)`: Fast single-day schedule lookups.
  - `idx_asset_ended_at` on `(asset_id, ended_at_utc)`: Instant resolution of `latestOfMany` relationships.
  - `idx_asset_started_at` on `(asset_id, started_at_utc)`: Fast state-machine transitions in `recordPing()`.
  - `idx_ended_at_utc` on `(ended_at_utc)`: Resolves the global navbar latest ping query in $O(1)$.
- `telematics_daily_metrics`:
  - `idx_metric_date` on `(metric_date)`: Fast date-range queries for KPI cards.

### Relationship Architecture
- Uses Laravel's native `hasOne()->latestOfMany('ended_at_utc')` relationship on `Asset::latestActivityInterval` to eliminate N+1 queries during inventory pagination.

---

## 🧪 Automated Testing

The application includes a complete suite of Unit and Feature tests with **100% pass rate**:

```bash
docker compose exec -u sail laravel.test php artisan test
```

### Test Coverage Highlights:
- **`TelematicsPingTest`**: Valid ping ingestion, validation anomaly email queuing, and 60-minute alert deduplication.
- **`AssetTest`**: Asset directory rendering, unique code validation, CRUD operations, cascading deletes.
- **`DashboardTest`**: Inertia dashboard rendering, partial JSON endpoints, global prop sharing (`appName`, `lastPing`).
- **`AssetActivityIntervalServiceTest`**: Initial ping recording, heartbeat extensions, state-machine transitions, midnight rollover splitting.
- **`TelematicsDashboardServiceTest`**: Duty-cycle percentage calculations, timeline slicing, traveled distance delta calculations.

---

## ⏰ Scheduled Cron Jobs

The application defines a daily rollup command to aggregate telemetry into `telematics_daily_metrics`:

```bash
# Run manual aggregation
docker compose exec -u sail laravel.test php artisan telematics:aggregate-daily

# Scheduled automatically in routes/console.php:
# Schedule::command('telematics:aggregate-daily')->dailyAt('00:05');
```

---

## 💡 Key Architectural & Domain Assumptions

During the implementation of this fleet telematics solution matching the assessment requirements, the following key engineering and domain assumptions were made:

### 1. Shift Window & Day Overview Calculations
- **Viewing Window (`11:00` - `18:00`)**: The Day Overview Gantt timeline focuses on the primary working shift across heavy equipment operating sites. All percentage calculations and horizontal ribbon slices clamp to this window boundaries so activity does not overflow visually.
- **Timezone Standardization**: Telemetry packets arrive with UTC timestamps, which are converted to the asset deployment timezone (`Asia/Dubai` by default) to determine local operating dates and shift intervals.

### 2. Operational Ratio Badges
- **Availability / Utilization Ratio (Sky Blue Badge)**: `(Total Running Minutes / Shift Window Minutes) * 100` (where running = active + idle minutes).
- **Idling Ratio (Orange Badge)**: `(Idle Minutes / Running Minutes) * 100`. Only displayed if the machine spent time idling.
- **Active Working Ratio (Teal Badge)**: `(Active Minutes / Running Minutes) * 100`. Only displayed if the machine worked during the shift.
- **Auxiliary Attachment Ratio (Bronze Badge)**: Represents specialized hydraulic/implement duty cycles when reported by onboard sensors.

### 3. State-Machine & Telemetry Heartbeats
- **Interval Extension (Heartbeats)**: Frequent pings with the same status (e.g. repeated `active` signals every 30 seconds) do not create redundant database rows. Instead, the backend extends the ongoing interval’s `ended_at_utc` and increments `duration_minutes`.
- **Midnight Splitting**: When machinery operates continuously past midnight (`23:59:59` to `00:00:00`), the service splits the interval at local midnight so daily operational metrics partition accurately across calendar dates.

### 4. Offline Detection Threshold
- An asset is designated **Offline** if no valid telemetry ping has been received for more than **24 consecutive hours**. This accounts for machines stationed in maintenance bays, off-shift storage, or remote zones with intermittent cellular gateway connectivity.

### 5. Anomaly Alerting & Deduplication
- Hardware pings with missing or corrupt CAN-bus parameters trigger HTTP 422, log a critical error, and queue an email alert to the fleet administrator on queue `notifications`.
- To prevent inbox flooding during hardware sensor flapping or CAN-bus loop errors, identical validation anomaly signatures from the same device are deduplicated and throttled for **60 minutes**.

---

## 📄 License
This application is open-sourced software licensed under the [MIT License](LICENSE).
