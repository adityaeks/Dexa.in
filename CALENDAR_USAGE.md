# Kalender Order - Guava Calendar Plugin

## Deskripsi
Plugin Guava Calendar telah berhasil diimplementasikan untuk menampilkan order berdasarkan `due_date` (tanggal jatuh tempo). Calendar ini memberikan visualisasi yang mudah untuk melihat jadwal order dan status pembayarannya.

## Fitur yang Tersedia

### 1. Visualisasi Order
- Menampilkan semua order berdasarkan tanggal jatuh tempo
- Warna event menunjukkan status pembayaran:
  - 🟢 **Hijau**: Lunas (Paid)
  - 🟡 **Kuning**: Sebagian (Partial) 
  - 🔴 **Merah**: Belum Bayar (Unpaid)
  - ⚫ **Abu-abu**: Status Tidak Diketahui

### 2. Interaksi Calendar
- **Klik Event**: Melihat detail order atau edit order
- **Klik Tanggal**: Membuat order baru dengan tanggal yang dipilih
- **Drag & Select Range**: Membuat order baru dengan range tanggal
- **Navigasi**: Pindah antar bulan, minggu, atau hari

### 3. View Calendar
- **Month View**: Tampilan bulan (default)
- **Week View**: Tampilan minggu
- **Day View**: Tampilan hari
- **List View**: Tampilan list

## Cara Mengakses

### 1. Melalui Dashboard
Calendar widget akan muncul di halaman dashboard sebagai widget.

### 2. Melalui Menu Navigasi
- Buka menu "Order Management"
- Klik "Kalender Order"

## Implementasi Teknis

### 1. Model Order
Model `Order` telah mengimplementasikan interface `Eventable`:
```php
class Order extends Model implements Eventable
{
    public function toCalendarEvent(): array|CalendarEvent
    {
        // Konfigurasi event calendar
    }
}
```

### 2. Widget Calendar
File: `app/Filament/Widgets/OrderCalendarWidget.php`
- Menggunakan plugin Guava Calendar
- Menampilkan order berdasarkan `due_date`
- Mendukung context menu untuk aksi

### 3. Halaman Calendar
File: `app/Filament/Pages/OrderCalendarPage.php`
- Halaman khusus untuk calendar
- Terintegrasi dengan widget calendar

## Konfigurasi

### 1. Tailwind CSS
File `tailwind.config.js` telah dikonfigurasi untuk mendukung plugin:
```javascript
content: [
    './vendor/guava/calendar/resources/**/*.blade.php',
]
```

### 2. Admin Panel
Widget dan halaman telah didaftarkan di `AdminPanelProvider.php`

## Penggunaan

### 1. Melihat Order
- Klik pada event di calendar
- Pilih "Lihat Order" untuk melihat detail
- Pilih "Edit Order" untuk mengedit

### 2. Membuat Order Baru
- Klik pada tanggal kosong
- Pilih "Buat Order Baru"
- Form akan terisi otomatis dengan tanggal yang dipilih

### 3. Filter dan Pencarian
- Gunakan navigasi calendar untuk pindah periode
- Switch antara view yang berbeda (month/week/day/list)

## Troubleshooting

### 1. Calendar Tidak Muncul
- Pastikan plugin Guava Calendar terinstall: `composer require guava/calendar`
- Jalankan: `php artisan filament:assets`
- Clear cache: `php artisan config:clear`

### 2. Event Tidak Tampil
- Pastikan order memiliki `due_date` yang tidak null
- Periksa relasi `customer` pada model Order
- Pastikan user memiliki permission untuk melihat order

### 3. Styling Tidak Tepat
- Pastikan Tailwind CSS terkonfigurasi dengan benar
- Jalankan: `npm run build` atau `npm run dev`

## Dependencies
- `guava/calendar`: Plugin calendar untuk Filament
- `vkurko/calendar`: Library calendar yang digunakan
- Filament v3.x atau v4.x

## Support
Untuk masalah teknis, silakan buka issue di repository project ini. 
