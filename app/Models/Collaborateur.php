<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collaborateur extends Model
{
   protected $fillable = [
    'user_id',
    'nom',
    'prenom',
    'numero_telephone',
    'poste',
    'date_recrutement',
    'etat'
   ];
   public function user(){
    return $this->belongsTo(User::class);
   }
}
