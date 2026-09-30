# 🚀 EventTix - Festival Event Ticketing & Concurrency Platform

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Midtrans](https://img.shields.io/badge/Midtrans-Payment_Gateway-0099FF?style=for-the-badge&logo=midtrans&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-77C1D2?style=for-the-badge&logo=alpine.js&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Pest PHP](https://img.shields.io/badge/Testing-Pest_PHP-000000?style=for-the-badge&logo=pest&logoColor=white)

**EventTix** adalah platform penjualan tiket festival & konser spektakuler berorientasi *high-concurrency* yang dirancang untuk mencegah **Overselling / Sold-Out Race Condition** pada pembelian kuota tiket secara bersamaan.

---

## 🌟 Fitur Utama & Keunggulan Teknis (Production-Ready)

1. **🎨 Rebranding & Single-Tab Streamlined Flow:**
   - Navigasi mulus dalam satu tab tanpa window bertele-tele (`target="_blank"`), langsung dari Katalog ➔ Checkout ➔ QR Code E-Ticket.
2. **💳 Integrasi Midtrans Payment & Revive Payment:**
   - Tombol **💳 Bayar Sekarang** pada menu "Tiket Saya" jika pop-up checkout tertutup secara tidak sengaja.
   - **Live Digital Countdown Timer** berbasis Alpine.js yang menghitung mundur 10 menit kuota reserve.
   - **Auto-Cancel & Kuota Refund Engine:** Background job otomatis membatalkan pesanan yang kadaluwarsa & mengembalikan sisa tiket ke kuota publik.
3. **📸 Gate Scanner Kamera Langsung (`html5-qrcode`):**
   - Petugas/Panitia dapat melakukan scan QR Code secara instan menggunakan kamera *smartphone* atau *laptop* via koneksi AJAX real-time.
4. **🎟️ E-Ticket Anti-Fraud & Watermark "TERPAKAI":**
   - E-Ticket menggunakan kode rapi (Contoh: `TKT-SP-XYZ123`).
   - Begitu tiket terpakai di gate, QR Code otomatis berubah menjadi redup (grayscale) dengan watermark raksasa **✅ TERPAKAI** untuk mencegah penipuan *double scan*.
5. **🔐 Role-Based Access Control (RBAC):**
   - **Admin:** Akses penuh analitik gross revenue, manajemen event, & gate scanner.
   - **Organizer:** Akses khusus Gate Scanner & riwayat check-in penonton.
   - **Customer:** Akses catalog, pemesanan tiket, & riwayat "Tiket Saya".

---

## 🏗️ System Architecture Flowchart

```mermaid
flowchart TD
    User[Pelanggan EventTix] -->|1. Pilih Event & Kategori Tiket| OrderService[OrderService & Concurrency Protection]
    OrderService -->|2. Atomic DB Lock & Deduct Quota| DB[(Database MySQL/SQLite)]
    DB -->|3. Create Pending Order| OrderTable[Orders Table - 10 Min Reserve]
    
    OrderTable -->|4. Dispatch Expiration Job| QueueWorker[Laravel Queue Worker]
    QueueWorker -->|If Unpaid in 10 Mins| Release[Cancel Order & Refund Quota]

    User -->|5. Checkout via Midtrans Snap| Midtrans[Midtrans Payment Sandbox]
    Midtrans -->|6. Payment Settlement Webhook| Webhook[Webhook Notification Handler]
    Webhook -->|7. Mark Order Confirmed| IssueTicket[Issue Unique QR Code E-Tickets]
    
    GateStaff[Panitia Gate Scanner] -->|8. Scan QR Code via Kamera Smartphone| Scanner[Gate Scanner AJAX]
    Scanner -->|9. Validate & Mark Checked-in| Watermark[Apply Watermark TERPAKAI]
```

---

## 🧪 Automated Test Suite (100% Green Pass)

- `test_user_can_successfully_order_ticket_and_reserve_quota`
- `test_prevents_overselling_when_quota_is_insufficient`
- `test_creates_snap_token_and_transaction_for_order`
- `test_handles_successful_webhook_payment_and_issues_tickets`
- `test_gate_scanner_validates_and_checks_in_ticket`
- `test_admin_can_access_dashboard_and_see_analytics`

---

## 🚀 Cara Menjalankan Lokal

```bash
# 1. Migration & Seeder Database
php artisan migrate:fresh --seed

# 2. Jalankan Queue Worker (Background Auto-Cancel)
php artisan queue:work

# 3. Jalankan Local Server
php artisan serve
```

Akses aplikasi di: `http://127.0.0.1:8000`  
Halaman Login: `http://127.0.0.1:8000/login`

---

## 📝 License
This project is open-sourced under the [MIT License](LICENSE).
