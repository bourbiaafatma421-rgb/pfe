<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- Ajout du trait

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // <-- HasApiTokens ajouté

    protected $fillable = [
        'role_id',
        'nom',
        'prenom',
        'email',
        'password',
        'numero_telephone',
        'active',
        'date_recrutement',
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
        public function hasRole(string $roleName): bool
    {
        return strtolower($this->role?->name ?? '') === strtolower($roleName);
    }


    // Helpers de rôle
    public function isCollaborateur()
    {
        return $this->role?->name === 'new_collaborateur';
    }

    public function isRh()
    {
        return $this->role?->name === 'rh';
    }

    public function isManager()
    {
        return $this->role?->name === 'manager';
    }
}
