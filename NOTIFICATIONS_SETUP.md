# Setup Database Notifications di Filament

## Status Setup Saat Ini ✅

Semua komponen notifications sudah terpasang dengan benar:

### 1. Database & Migration ✅
- Tabel `notifications` sudah dibuat dan migrated
- Migration: `2025_07_22_145438_create_notifications_table.php`

### 2. Model Configuration ✅
- Model `User` sudah menggunakan trait `Notifiable`
- Model `Akademisi` sudah menggunakan trait `Notifiable`
- Model `Customer` sudah menggunakan trait `Notifiable`

### 3. Filament Panel Configuration ✅
- AdminPanelProvider sudah dikonfigurasi dengan:
  - `->databaseNotifications()`
  - `->databaseNotificationsPolling('30s')`
  - Queue connection otomatis diset ke `sync` untuk development

### 4. Queue Configuration ✅
- Queue connection default: `database`
- Untuk development: otomatis diset ke `sync` di AdminPanelProvider
- Queue worker sudah berjalan dan memproses notifications

## Cara Menggunakan Notifications

### 1. Toast Notifications (Tampilan Sementara)
```php
use Filament\Notifications\Notification;

// Di dalam action atau method
Notification::make()
    ->title('Berhasil!')
    ->body('Data telah disimpan')
    ->success()
    ->send();
```

### 2. Database Notifications (Tersimpan di Database)
```php
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

// Kirim ke user yang sedang login
$user = Auth::user();
$user->notify(
    Notification::make()
        ->title('Data Berhasil Dibuat!')
        ->body('Data ' . $record->name . ' telah dibuat')
        ->toDatabase()
);

// Atau gunakan sendToDatabase
Notification::make()
    ->title('Data Berhasil Dibuat!')
    ->sendToDatabase($user);
```

### 3. Contoh Implementasi di Resource Pages

#### CreateRecord Page
```php
protected function afterCreate(): void
{
    $user = Auth::user();
    
    // Database notification
    $user->notify(
        Notification::make()
            ->title('Data Berhasil Dibuat!')
            ->body('Data ' . $this->record->name . ' telah dibuat')
            ->toDatabase()
    );
    
    // Toast notification
    Notification::make()
        ->title('Data Berhasil Dibuat!')
        ->success()
        ->send();
}
```

#### EditRecord Page
```php
protected function afterSave(): void
{
    $user = Auth::user();
    
    $user->notify(
        Notification::make()
            ->title('Data Berhasil Diupdate!')
            ->body('Data ' . $this->record->name . ' telah diupdate')
            ->toDatabase()
    );
}
```

#### Action Buttons
```php
use Filament\Actions\Action;

Action::make('approve')
    ->action(function () {
        // Logic approval
        
        // Send notification
        $user = Auth::user();
        $user->notify(
            Notification::make()
                ->title('Approval Berhasil!')
                ->body('Data telah diapprove')
                ->toDatabase()
        );
    })
```

## Troubleshooting

### 1. Notifications Tidak Muncul di Database
**Penyebab:** Queue worker tidak berjalan
**Solusi:**
```bash
# Jalankan queue worker
php artisan queue:work

# Atau untuk development, gunakan sync queue
php artisan queue:set-sync
```

### 2. Icon Bell Tidak Muncul
**Penyebab:** Database notifications tidak diaktifkan di panel
**Solusi:** Pastikan AdminPanelProvider memiliki:
```php
->databaseNotifications()
->databaseNotificationsPolling('30s')
```

### 3. Model Tidak Bisa Menerima Notifications
**Penyebab:** Model tidak menggunakan trait Notifiable
**Solusi:** Tambahkan trait ke model:
```php
use Illuminate\Notifications\Notifiable;

class YourModel extends Model
{
    use Notifiable;
    // ...
}
```

## Testing Notifications

### Test Manual dengan Tinker
```bash
php artisan tinker

# Kirim test notification
use App\Models\User;
use Filament\Notifications\Notification;

$user = User::first();
$user->notify(
    Notification::make()
        ->title('Test Notification')
        ->body('Ini adalah test')
        ->toDatabase()
);

# Cek jumlah notifications
echo DB::table('notifications')->count();
```

### Test dengan Script
```bash
php test_notification.php
```

## Best Practices

1. **Gunakan Toast untuk Feedback Langsung:** Untuk aksi yang memerlukan feedback segera
2. **Gunakan Database untuk Riwayat:** Untuk notifikasi yang perlu disimpan dan dilihat nanti
3. **Kombinasikan Keduanya:** Untuk UX yang optimal
4. **Gunakan Queue untuk Production:** Untuk performa yang lebih baik
5. **Gunakan Sync untuk Development:** Untuk testing yang lebih mudah

## Resource yang Sudah Menggunakan Notifications

- ✅ `AkademisiResource/Pages/CreateAkademisi.php`
- ✅ `OrderResource/Pages/CreateOrder.php`
- ✅ `CustomerResource/Pages/CreateCustomer.php`

## Next Steps

1. Tambahkan notifications ke resource lainnya
2. Implementasikan notifikasi untuk aksi-aksi penting
3. Customize tampilan notifications sesuai kebutuhan
4. Setup email notifications jika diperlukan
