<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // protected $table = 'users';
    protected $guarded = ['id'];  

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo',
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
    }

    // Helper to produce a data URL string for inline <img>
    public function getProfilePhotoDataUrlAttribute(): ?string
    {
        if (!$this->profile_photo || !$this->profile_photo_mime) {
            return null;
        }
        $base64 = base64_encode($this->profile_photo);
        return 'data:' . $this->profile_photo_mime . ';base64,' . $base64;
    }
}
