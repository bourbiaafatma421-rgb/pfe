<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
<<<<<<< HEAD
    use HasFactory;
    protected $table = 'role';
    protected $fillable = ['name'];
=======
    
    protected $fillable = [
        'name',
    ];
>>>>>>> origin/main
    public function users(){
        return $this->hasMany(User::class);
   }
}
