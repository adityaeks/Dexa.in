# 🔧 Environment Variables untuk Google Calendar

## Tambahkan ke file `.env`

```env
# Google Calendar Configuration
GOOGLE_CALENDAR_ID=your_calendar_id_here
GOOGLE_CALENDAR_AUTH_PROFILE=service_account
GOOGLE_CALENDAR_IMPERSONATE_USER_EMAIL=your_email@gmail.com
GOOGLE_CALENDAR_CACHE_STORE=default
GOOGLE_CALENDAR_CACHE_PREFIX=google_calendar_
GOOGLE_CALENDAR_CACHE_TTL=3600
```

## Penjelasan Variables

### `GOOGLE_CALENDAR_ID`
- **Required**: Ya
- **Format**: Calendar ID dari Google Calendar
- **Contoh**: `abc123def456@group.calendar.google.com`
- **Cara Dapat**: 
  1. Buka Google Calendar
  2. Settings calendar yang ingin digunakan
  3. Tab "Integrate calendar"
  4. Copy "Calendar ID"

### `GOOGLE_CALENDAR_AUTH_PROFILE`
- **Required**: Ya
- **Value**: `service_account` (untuk service account) atau `oauth` (untuk OAuth2)
- **Default**: `service_account`
- **Rekomendasi**: Gunakan `service_account` untuk production

### `GOOGLE_CALENDAR_IMPERSONATE_USER_EMAIL`
- **Required**: Tidak (opsional)
- **Format**: Email Google account yang ingin di-impersonate
- **Contoh**: `admin@yourcompany.com`
- **Catatan**: Hanya diperlukan jika menggunakan domain-wide delegation

### `GOOGLE_CALENDAR_CACHE_STORE`
- **Required**: Tidak
- **Default**: `default`
- **Value**: Cache store yang digunakan untuk menyimpan access token
- **Contoh**: `redis`, `file`, `database`

### `GOOGLE_CALENDAR_CACHE_PREFIX`
- **Required**: Tidak
- **Default**: `google_calendar_`
- **Value**: Prefix untuk cache key
- **Contoh**: `gc_`, `calendar_`

### `GOOGLE_CALENDAR_CACHE_TTL`
- **Required**: Tidak
- **Default**: `3600` (1 jam dalam detik)
- **Value**: Time to live untuk cache access token
- **Contoh**: `7200` (2 jam), `1800` (30 menit)

## Contoh Lengkap

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dexain_project
DB_USERNAME=root
DB_PASSWORD=

# Google Calendar
GOOGLE_CALENDAR_ID=dexain_orders@group.calendar.google.com
GOOGLE_CALENDAR_AUTH_PROFILE=service_account
GOOGLE_CALENDAR_CACHE_STORE=redis
GOOGLE_CALENDAR_CACHE_PREFIX=gc_
GOOGLE_CALENDAR_CACHE_TTL=7200
```

## Setup Langkah demi Langkah

### 1. Dapatkan Calendar ID
```bash
# Buka Google Calendar di browser
# Settings > Calendar Settings > Integrate calendar
# Copy Calendar ID
```

### 2. Update .env
```bash
# Edit file .env
nano .env

# Tambahkan Google Calendar variables
GOOGLE_CALENDAR_ID=your_calendar_id_here
GOOGLE_CALENDAR_AUTH_PROFILE=service_account
```

### 3. Setup Credentials
```bash
# Buat folder untuk credentials
mkdir -p storage/app/google-calendar

# Copy file credentials dari Google Cloud Console
# Rename menjadi service-account-credentials.json
cp ~/Downloads/your-project-credentials.json storage/app/google-calendar/service-account-credentials.json
```

### 4. Test Konfigurasi
```bash
# Clear config cache
php artisan config:clear

# Test koneksi
php artisan google-calendar:sync
```

## Troubleshooting

### Error: Calendar ID tidak valid
```
Error: Invalid calendar ID
```
**Solusi**: 
- Pastikan Calendar ID benar
- Format: `xxx@group.calendar.google.com` atau `primary`
- Cek di Google Calendar Settings

### Error: Service account tidak memiliki akses
```
Error: Access denied
```
**Solusi**:
- Tambahkan service account email ke calendar
- Berikan permission "Make changes to events"
- Cek apakah service account aktif

### Error: Credentials tidak ditemukan
```
Error: Credentials file not found
```
**Solusi**:
- Pastikan file `service-account-credentials.json` ada
- Path: `storage/app/google-calendar/service-account-credentials.json`
- Cek permission file

### Error: Cache tidak bisa diakses
```
Error: Cache store not available
```
**Solusi**:
- Pastikan cache store yang dikonfigurasi tersedia
- Gunakan `default` untuk cache file
- Atau setup Redis/database cache

## Security Best Practices

### 1. Environment Variables
- Jangan hardcode credentials di kode
- Gunakan environment variables
- Jangan commit `.env` ke git

### 2. File Credentials
- Simpan di folder yang aman
- Set permission file yang tepat
- Backup credentials secara aman

### 3. Calendar Permissions
- Berikan permission minimal yang diperlukan
- Jangan share calendar dengan permission penuh
- Monitor akses secara berkala

### 4. Cache Security
- Gunakan cache yang aman (Redis dengan auth)
- Set TTL yang reasonable
- Monitor cache usage

## Monitoring

### Log Files
```bash
# Monitor log Google Calendar
tail -f storage/logs/laravel.log | grep "Google Calendar"

# Cek error
grep -i "google calendar" storage/logs/laravel.log
```

### Cache Status
```bash
# Cek cache Google Calendar
php artisan cache:table
php artisan tinker
>>> Cache::get('google_calendar_access_token')
```

### Test Connection
```bash
# Test koneksi manual
php artisan tinker
>>> $service = new App\Services\GoogleCalendarService();
>>> $service->syncAllOrders();
```
