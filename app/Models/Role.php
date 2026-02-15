<?php

namespace App\Models;
<<<<<<< HEAD
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
=======
>>>>>>> 5644ad7 (amelioration en cours)
=======
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
    use HasFactory;
    protected $table = 'role';
    protected $fillable = ['nom'];
    public function users(){
        return $this->hasMany(User::class);
    }
=======
=======
    
>>>>>>> b18fa01 (changement)
=======
    
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
    protected $fillable = [
        'name',
    ];
    public function users(){
        return $this->hasMany(User::class);
   }
<<<<<<< HEAD
>>>>>>> 5644ad7 (amelioration en cours)
=======
>>>>>>> b18fa01a33003921548a3aec3cf4c40ce7a8a510
}
