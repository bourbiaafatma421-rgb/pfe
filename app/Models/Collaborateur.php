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
   protected $appends = ['email'];
   protected $with = ['user'];
   protected $hidden = ['user_id', 'user'];
   public function user(){
    return $this->belongsTo(User::class);
   }
   public function getEmailAttribute(){
        return $this->user?->email;
   }
}
