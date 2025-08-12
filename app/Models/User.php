<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;


class User extends Authenticatable implements HasAvatar
{
    // Untuk kompatibilitas Filament avatar (method, bukan attribute)
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            // Hapus penambahan 'avatars/' jika sudah ada
            $path = $this->avatar_url;
            return asset('storage/' . $path);
        }
        return null;
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url', // untuk avatar profile filament
    ];
    // Untuk mendukung avatar profile Filament Edit Profile
    public function getFilamentAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_url) {
            $path = str_starts_with($this->avatar_url, 'avatars/')
                ? $this->avatar_url
                : 'avatars/' . $this->avatar_url;
            return asset('storage/' . $path);
        }
        return null;
    }

    /**
     * Hapus file avatar lama jika avatar dihapus atau diganti.
     */
    public function setAvatarUrlAttribute($value)
    {
        // Jika avatar lama ada dan berbeda dengan yang baru, hapus file lama
        if (!empty($this->attributes['avatar_url']) && $this->attributes['avatar_url'] !== $value) {
            $oldPath = $this->attributes['avatar_url'];
            // Pastikan path tidak double 'avatars/'
            if (!str_starts_with($oldPath, 'avatars/')) {
                $oldPath = 'avatars/' . $oldPath;
            }
            Storage::disk(config('filament-edit-profile.disk', 'public'))->delete($oldPath);
        }
        $this->attributes['avatar_url'] = $value;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
