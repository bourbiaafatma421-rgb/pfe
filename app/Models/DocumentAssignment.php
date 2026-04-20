<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Document;
use App\Models\User;
use App\Models\DocumentSignature;

class DocumentAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['document_id', 'user_id', 'assigned_by', 'status', 'signed_at'];

    public function document()  { 
        return $this->belongsTo(Document::class); 
    }
    public function collaborateur() {
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function assignedBy() { 
        return $this->belongsTo(User::class, 'assigned_by'); 
    }

    public function signaturePath(): ?string
    {
        return $this->collaborateur ? $this->collaborateur->signature_path : null;
    }
}