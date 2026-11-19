<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'google_id',
        'avatar',
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

    // Scope for admin and dosen (evaluator) users
    public function scopeAdminAndEvaluator($query): void
    {
        $query->whereIn('role', ['admin', 'dosen']);
    }

    /**
     * Backwards-compatible accessor so templates can use $user->name
     * while the actual column is `nama_user` in this project.
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['nama_user'] ?? $this->attributes['username'] ?? '';
    }

    public function mataKuliahsPengampu(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'dosen_mata_kuliah', 'dosen_id', 'mata_kuliah_id')
            ->withTimestamps();
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(\App\Models\Mahasiswa::class);
    }

    public function getOrResolveMahasiswa(): ?\App\Models\Mahasiswa
    {
        return \App\Models\Mahasiswa::resolveForUser($this);
    }

    public function dosen(): HasOne
    {
        return $this->hasOne(\App\Models\Dosen::class);
    }

    public function penugasanPenilai(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ProjectDosenPenilai::class, 'dosen_id');
    }

    public function penilaianProyek(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PenilaianProyekDosen::class, 'dosen_id');
    }

    public function proyekDitentukan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ProjectCard::class, 'dosen_penentu_id');
    }

    /**
     * Otomatis memvalidasi dan memulihkan role dosen jika user tercatat di data dosen / pengampu.
     */
    public function syncAndFixRole(): void
    {
        if ($this->role === 'mahasiswa') {
            $isActuallyDosen = \App\Models\Dosen::where('user_id', $this->id)->exists()
                || \App\Models\Dosen::where('nip', $this->username)->exists()
                || \Illuminate\Support\Facades\DB::table('dosen_mata_kuliah')->where('dosen_id', $this->id)->exists()
                || \App\Models\ProjectDosenPenilai::where('dosen_id', $this->id)->exists()
                || \App\Models\ProjectCard::where('dosen_penentu_id', $this->id)->exists();

            if ($isActuallyDosen) {
                $this->role = 'dosen';
                $this->save();

                $bankDosen = \App\Models\Dosen::where('nip', $this->username)
                    ->orWhere('user_id', $this->id)
                    ->first();

                if ($bankDosen && ! $bankDosen->user_id) {
                    $bankDosen->user_id = $this->id;
                    $bankDosen->save();
                }

                \App\Models\Mahasiswa::where('user_id', $this->id)->whereNull('nim')->delete();
            }
        }
    }

    public function isProfileComplete(): bool
    {
        // Untuk mahasiswa, wajib memiliki no_hp dan data akademik (NIM, kelas_id, dan is_profile_complete)
        if ($this->role === 'mahasiswa') {
            $mhs = $this->getOrResolveMahasiswa();
            if (! $mhs) {
                return false;
            }

            return ! empty($this->no_hp) && ! empty($mhs->nim) && ! empty($mhs->kelas_id) && (bool) $mhs->is_profile_complete;
        }

        // Untuk role lain (admin, dosen), wajib melengkapi no_hp
        return ! empty($this->no_hp);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEvaluator(): bool
    {
        // Dosen bertindak langsung sebagai evaluator/penilai proyek
        return $this->role === 'dosen';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function scopeDosen($query): void
    {
        $query->where('role', 'dosen');
    }
}
