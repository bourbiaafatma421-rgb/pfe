<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use App\Models\DocumentAssignment;
use App\Models\DocumentSignature;

class Document extends Model
{
    use HasFactory;
    protected $table = 'documents';
    protected $fillable=['namedoc', 'path', 'signature_req'];
    protected $casts = [
        'signature_req' => 'boolean',
    ];
    public function requiresSignature():bool{
        return $this->signature_req;
    }
    public function signatures() {
        return $this->hasMany(DocumentSignature::class);
    }
    public function assignments()
    {
        return $this->hasMany(DocumentAssignment::class);
    }
}
