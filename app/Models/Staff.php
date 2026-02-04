<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = [
        'user_id',
        'role',
        'nom',
        'prenom'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
