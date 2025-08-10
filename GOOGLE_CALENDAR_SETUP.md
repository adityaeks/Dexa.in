# 🗓️ Setup Google Calendar Integration

## Deskripsi
Integrasi Google Calendar memungkinkan sistem untuk secara otomatis membuat, mengupdate, dan menghapus event di Google Calendar ketika order dibuat, diupdate, atau dihapus.

## Fitur
- ✅ **Auto Create**: Event Google Calendar otomatis dibuat saat order dibuat
- ✅ **Auto Update**: Event Google Calendar otomatis diupdate saat order diupdate
- ✅ **Auto Delete**: Event Google Calendar otomatis dihapus saat order dihapus
- ✅ **Color Coding**: Warna event berdasarkan status pembayaran
- ✅ **Source URL**: Link langsung ke order di sistem
- ✅ **Sync Command**: Command untuk sync manual

## Setup Google Calendar API

### 1. Buat Google Cloud Project
1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang sudah ada
3. Aktifkan Google Calendar API

### 2. Buat Service Account
1. Di Google Cloud Console, buka "APIs & Services" > "Credentials"
2. Klik "Create Credentials" > "Service Account"
3. Isi nama service account (misal: "dexain-calendar")
4. Klik "Create and Continue"
5. Skip role assignment, klik "Done"

### 3. Download Credentials
1. Klik service account yang baru dibuat
2. Tab "Keys" > "Add Key" > "Create new key"
3. Pilih "JSON" format
4. Download file JSON

### 4. Setup Google Calendar
1. Buka [Google Calendar](https://calendar.google.com/)
2. Buat calendar baru atau gunakan calendar yang sudah ada
3. Di settings calendar, tab "Share with specific people"
4. Tambahkan email service account (dari file JSON yang didownload)
5. Berikan permission "Make changes to events"
6. Copy Calendar ID dari tab "Integrate calendar"

### 5. Setup File Credentials
1. Buat folder: `storage/app/google-calendar/`
2. Simpan file JSON credentials dengan nama: `service-account-credentials.json`
3. Pastikan file tidak masuk ke git (tambahkan ke .gitignore)

### 6. Setup Environment Variables
Tambahkan ke file `.env`:
```env
GOOGLE_CALENDAR_ID=your_calendar_id_here
GOOGLE_CALENDAR_AUTH_PROFILE=service_account
```

## Konfigurasi

### File Config
File config sudah dibuat di `config/google-calendar.php` dengan pengaturan default:
- Service account credentials: `storage/app/google-calendar/service-account-credentials.json`
- Calendar ID: dari environment variable `GOOGLE_CALENDAR_ID`
- Auth profile: `service_account`

### Model Order
Kolom `google_calendar_event_id` sudah ditambahkan ke tabel `orders` untuk menyimpan ID event Google Calendar.

## Penggunaan

### 1. Otomatis
Event Google Calendar akan dibuat/update/hapus otomatis saat:
- Order dibuat (CreateOrder)
- Order diupdate (EditOrder)  
- Order dihapus (DeleteAction)

### 2. Manual Sync
Jalankan command untuk sync manual:
```bash
# Sync order yang belum memiliki Google Calendar event
php artisan google-calendar:sync

# Force sync semua order (hapus dan buat ulang)
php artisan google-calendar:sync --force
```

### 3. Service Class
Gunakan `GoogleCalendarService` untuk operasi manual:
```php
use App\Services\GoogleCalendarService;

$service = new GoogleCalendarService();

// Buat event
$event = $service->createEventFromOrder($order);

// Update event
$success = $service->updateEventFromOrder($order);

// Hapus event
$success = $service->deleteEventFromOrder($order);

// Sync semua order
$result = $service->syncAllOrders();
```

## Format Event Google Calendar

### Judul Event
```
{nomer_nota} - {customer_name}
```

### Deskripsi Event
```
Order: {nomer_nota}
Customer: {customer_name}
Total: Rp {total_harga}
Status Pembayaran: {status_payment}
Status Order: {status}
Keterangan: {keterangan} (jika ada)
```

### Warna Event
- 🟢 **Hijau (Color ID 2)**: Lunas (paid)
- 🟡 **Orange (Color ID 5)**: Sebagian (partial)
- 🔴 **Merah (Color ID 11)**: Belum Bayar (unpaid)
- ⚫ **Abu-abu (Color ID 8)**: Status tidak diketahui

### Source URL
Link langsung ke halaman edit order di sistem admin.

## Troubleshooting

### 1. Event Tidak Terbuat
- Cek apakah `due_date` order tidak null
- Cek log Laravel untuk error detail
- Pastikan credentials Google Calendar benar
- Pastikan calendar ID benar

### 2. Permission Error
- Pastikan service account email sudah ditambahkan ke calendar
- Pastikan permission "Make changes to events" diberikan
- Cek apakah calendar ID benar

### 3. Credentials Error
- Pastikan file JSON credentials ada di `storage/app/google-calendar/`
- Pastikan format file JSON benar
- Cek apakah service account email aktif

### 4. Sync Command Error
```bash
# Cek log error
tail -f storage/logs/laravel.log

# Test koneksi Google Calendar
php artisan tinker
>>> $service = new App\Services\GoogleCalendarService();
>>> $service->syncAllOrders();
```

## Log dan Monitoring

### Log Events
Semua operasi Google Calendar akan di-log di `storage/logs/laravel.log`:
- Success: Event berhasil dibuat/update/hapus
- Warning: Event gagal dibuat/update/hapus
- Error: Exception saat operasi

### Monitoring
- Cek log secara berkala untuk error
- Monitor jumlah event yang berhasil/gagal sync
- Pastikan calendar tidak penuh (Google Calendar limit)

## Security

### File Credentials
- Jangan commit file credentials ke git
- Tambahkan `storage/app/google-calendar/` ke `.gitignore`
- Backup file credentials secara aman

### Permissions
- Service account hanya memiliki akses ke calendar yang ditentukan
- Tidak ada akses ke calendar lain
- Permission minimal yang diperlukan

## Dependencies
- `spatie/laravel-google-calendar`: Package untuk integrasi Google Calendar
- `google/apiclient`: Google API Client Library
- Laravel 10+ dengan PHP 8.1+

## Support
Untuk masalah teknis, silakan buka issue di repository project ini dengan detail:
- Error message lengkap
- Log Laravel
- Konfigurasi yang digunakan
- Langkah reproduksi
