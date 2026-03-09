<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; 

    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'active',
        'date_of_hire',
        'password_changed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relation vers le rôle
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    // Vérifie un rôle en ignorant la casse
    public function hasRole(string $roleName): bool
    {
        return strtolower(trim($this->role->name ?? '')) === strtolower(trim($roleName));
    }
    

    // Helpers de rôle
    public function isCollaborateur(): bool
    {
        return $this->role && strtolower($this->role->name) === 'new_collaborateur';
    }

    public function isRh(): bool
    {
        return $this->role && strtolower($this->role->name) === 'rh';
    }

    public function isManager(): bool
    {
        return $this->role && strtolower($this->role->name) === 'manager';
    }
}
