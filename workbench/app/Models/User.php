<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Workbench\Database\Factories\UserFactory;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
