<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\Rule;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MustVerifyEmailTrait, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'foto_profil',
    ];

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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Satu sumber kebenaran untuk aturan validasi username — dipakai
     * ProfileUpdateRequest (Profil Saya) dan UserController (Manajemen
     * User) supaya kedua tempat itu tidak pernah punya aturan yang beda
     * sendiri-sendiri. $ignoreId adalah id user yang boleh tetap memakai
     * username-nya sendiri (dirinya sendiri saat update, null saat create).
     *
     * @return array<int, mixed>
     */
    public static function usernameRules(?int $ignoreId = null): array
    {
        return [
            'nullable',
            'string',
            'lowercase',
            'min:3',
            'max:30',
            'regex:/^[a-z0-9_.]+$/',
            Rule::unique(self::class, 'username')->ignore($ignoreId),
        ];
    }

    /**
     * True kalau user ini satu-satunya Admin yang tersisa.
     *
     * Dipakai sebagai rem terakhir supaya sistem tidak pernah sampai punya 0 Admin —
     * kondisi itu mengunci Manajemen User dan Penghapusan Aset secara permanen,
     * karena keduanya hanya bisa diakses role admin dan tidak ada jalur registrasi
     * mandiri (route register sengaja dinonaktifkan).
     */
    public function isLastAdmin(): bool
    {
        return $this->isAdmin()
            && static::where('role', 'admin')->where('id', '!=', $this->id)->doesntExist();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class, 'created_by');
    }
}
