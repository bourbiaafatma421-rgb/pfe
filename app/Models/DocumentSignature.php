<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $table = 'document_signatures';

    protected $fillable = [
        'document_id',
        'user_id',
        'signature_path',
        'signed_at',
        'status',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];


    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }
    public function sign(string $signaturePath): void
    {
        $this->signature_path = $signaturePath;
        $this->signed_at = now();
        $this->status = 'signed';
        $this->save();
    }
}
