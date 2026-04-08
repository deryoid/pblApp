<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_user',
        'username',
        'email',
        'password',
        'role',
        'no_hp',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo',
        'profile_photo_mime',
    ];

    /**
     * The attributes that should be visible in arrays/JSON serialization.
     *
     * @var list<string>
     */
    protected $visible = [
        'id',
        'uuid',
        'nama_user',
        'email',
        'username',
        'role',
        'no_hp',
        'email_verified_at',
        'created_at',
        'updated_at',
        'profile_photo_data_url', // Safe accessor method (backward compatible)
        'profile_photo_url', // New file-based storage
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

    // Use UUID for route-model binding
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Auto-generate UUID on create if missing
    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });

        static::updated(function (self $user) {
            // Clear dashboard cache when user data changes
            Cache::forget('mahasiswa_dashboard_'.$user->id);
        });

        static::deleted(function (self $user) {
            // Clear dashboard cache when user is deleted
            Cache::forget('mahasiswa_dashboard_'.$user->id);
        });
    }

    // Helper to produce a data URL string for inline <img> (backward compatibility)
    public function getProfilePhotoDataUrlAttribute(): ?string
    {
        // First try to get from new file storage
        if ($this->profile_photo_path) {
            return asset('storage/'.$this->profile_photo_path);
        }

        // Fallback to old blob storage
        if ($this->profile_photo && $this->profile_photo_mime) {
            $base64 = base64_encode($this->profile_photo);

            return 'data:'.$this->profile_photo_mime.';base64,'.$base64;
        }

        return null;
    }

    // Get profile photo URL
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if ($this->profile_photo_path) {
            return asset('storage/'.$this->profile_photo_path);
        }

        return null;
    }

    // Scope for admin and evaluator users
    public function scopeAdminAndEvaluator($query): void
    {
        $query->whereIn('role', ['admin', 'evaluator']);
    }

    /**
     * Backwards-compatible accessor so templates can use $user->name
     * while the actual column is `nama_user` in this project.
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['nama_user'] ?? $this->attributes['username'] ?? '';
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(\App\Models\Mahasiswa::class);
    }
}
