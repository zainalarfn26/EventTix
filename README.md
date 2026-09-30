# 🎟️ SeatPulse - Real-Time Event Ticketing & Seat Reservation System

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-Mutex_Lock-DC382D?style=for-the-badge&logo=redis&logoColor=white)
![Laravel Reverb](https://img.shields.io/badge/WebSockets-Laravel_Reverb-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Pest PHP](https://img.shields.io/badge/Testing-Pest_PHP-000000?style=for-the-badge&logo=pest&logoColor=white)

**SeatPulse** adalah platform reservasi tiket konser & event berorientasi *high-concurrency* yang dirancang untuk mengatasi permasalahan **Double-Booking (Race Condition)** saat ribuan pengguna mencoba memesan kursi yang sama secara bersamaan.

---

## 🎯 Key Architectural Highlights (Technical Selling Points)

- **🛡️ Race Condition & Double-Booking Protection:**
  Menggabungkan **Redis Distributed Mutex (`Cache::lock()`)** dan **Database Pessimistic Locking (`lockForUpdate()`)** untuk menjamin *atomic transaction* pada setiap pemesanan kursi.
- **⚡ Real-Time WebSockets Integration (Laravel Reverb):**
  Perubahan status kursi (`Available` ➔ `Locked` ➔ `Booked`) disiarkan secara instant tanpa reload halaman ke semua pengguna yang sedang berada di layar venue.
- **⏱️ Automated Seat Hold Expiration:**
  Kursi dikunci secara temporary selama **10 menit**. Jika pembayaran tidak diselesaikan, **Laravel Queue Worker** melepaskan kembali kursi ke pool publik.
- **💳 Midtrans Payment Gateway Integration:**
  Handling webhook pembayaran (`settlement`, `expire`, `cancel`, `deny`) dilengkapi dengan verifikasi **SHA512 Signature Key** & logika **Idempotency**.
- **📲 E-Ticket QR Code Generator & Gate Check-in:**
  Tiket terkonfirmasi dikirimkan otomatis dalam format PDF ber-QR Code via **Email Queue**, dilengkapi interface **Gate Scanner** untuk panitia mengabsahkan kedatangan pengunjung.
- **🧪 Comprehensive Automated Concurrency Testing:**
  Dilengkapi unit & feature tests menggunakan **Pest PHP** yang mensimulasikan request concurrent paralel.

---

## 🏗️ System Architecture

```mermaid
flowchart TD
    User A[User A - Browser] -->|1. Select Seat A1| LockService[SeatLockService]
    User B[User B - Browser] -->|1. Select Seat A1 concurrently| LockService

    LockService -->|2. Redis Mutex Lock| Redis[(Redis Cache)]
    Redis -->|Allowed| DB[MySQL Database Pessimistic Lock]
    Redis -->|Denied| Error[User B: Seat Currently Locked]

    DB -->|3. Create Pending Reservation| ResTable[Reservations Table]
    ResTable -->|4. Broadcast Event| Reverb[Laravel Reverb WebSockets]
    Reverb -->|5. Update Seat Color to Yellow| User A & User B

    ResTable -->|6. Dispatch 10-Min Expiration Job| Queue[Laravel Queue Worker]
    User A -->|7. Pay via Snap| Midtrans[Midtrans Payment Gateway]
    Midtrans -->|8. Webhook Notification| Webhook[Payment Webhook Handler]
    Webhook -->|9. Settlement Confirmed| TicketGen[QR Ticket Generator & Email Queue]
```

---

## 🗄️ Database Entity Relationship Diagram (ERD)

- **`venues`**: Menyimpan data venue & denah (rows, columns, categories).
- **`events`**: Data event, tanggal, venue, & daftar harga tiket.
- **`seats`**: Data nomor kursi (row, col, category).
- **`reservations`**: Status kunci temporary (`pending`, `confirmed`, `expired`, `cancelled`, `expires_at`).
- **`transactions`**: Log pembayaran Midtrans (`order_id`, `snap_token`, `gross_amount`, `payment_status`).
- **`tickets`**: E-ticket unik (`ticket_code`, QR Hash, `checked_in_at`).

---

## 🚀 Installation & Local Development Setup

### Requirements
- PHP >= 8.2
- Composer 2.x
- Node.js >= 20.x
- MySQL / PostgreSQL
- Redis Server (or Memcached/Database fallback for testing)

### Setup Steps
```bash
# 1. Clone repository
git clone https://github.com/username/seatpulse.git
cd seatpulse

# 2. Install PHP & Node dependencies
composer install
npm install

# 3. Setup environment configuration
cp .env.example .env
php artisan key:generate

# 4. Configure Database & Redis in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seatpulse
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis

# 5. Run Database Migrations & Seeders
php artisan migrate --seed

# 6. Run Reverb WebSocket Server & Queue Worker
php artisan reverb:start
php artisan queue:work

# 7. Start Vite & Local Laravel Development Server
npm run dev
php artisan serve
```

---

## 🧪 Running Automated Tests

```bash
# Run Pest test suite
php artisan test

# Run specific concurrency feature test
php artisan test --filter=SeatConcurrencyTest
```

---

## 📝 License
This project is open-sourced under the [MIT License](LICENSE).
