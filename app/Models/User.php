<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'nom',
        'prenom',
        'email',
        'nom',
        'prenom',
        'password',
<<<<<<< HEAD
        'role_id',
        'active',
        'date_recrutement',
        'numero_telephone',
        'password_changed',
=======
        'numero_telephone',
        'active',
        'date_recrutement',
        'password_changed'
>>>>>>> 5644ad7 (amelioration en cours)
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
    protected $casts = [
<<<<<<< HEAD
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
        'password_changed' => 'boolean',
        'date_recrutement'=>'date',
    ];
    
=======
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
];
>>>>>>> 5644ad7 (amelioration en cours)
    public function role(){
        return $this->belongsTo(Role::class);
    }
    public function hasRole(string $roleName): bool
    {
<<<<<<< HEAD
        if (!$this->role) {
            return false;
        }

        return strtolower($this->role->nom) === strtolower($roleName);
=======
        return $this->role?->name === 'new_collaborateur';
    }

    public function isRh()
    {
        return $this->role?->name  === 'rh';
    }

    public function isManager()
    {
        return $this->role?->name === 'manager';
>>>>>>> 5644ad7 (amelioration en cours)
    }
}
