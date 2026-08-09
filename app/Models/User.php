<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_KEPALA_SEKOLAH = 'kepala_sekolah';

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'is_active', 'last_login_at'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_active' => 'boolean', 'last_login_at' => 'datetime', 'password' => 'hashed'];

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function roleLabel(): string
    {
        return $this->isAdmin() ? 'Admin / Operator' : 'Kepala Sekolah';
    }
}
