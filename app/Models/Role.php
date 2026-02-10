<?php

namespace App\Models;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
=======
>>>>>>> 5644ad7 (amelioration en cours)

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
<<<<<<< HEAD
    use HasFactory;
    protected $table = 'role';
    protected $fillable = ['nom'];
    public function users(){
        return $this->hasMany(User::class);
    }
=======
    protected $fillable = [
        'name',
    ];
    public function user(){
        return $this->hasMany(User::class);
   }
>>>>>>> 5644ad7 (amelioration en cours)
}
