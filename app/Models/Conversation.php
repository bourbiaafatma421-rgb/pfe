<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Message;

class Conversation extends Model
{
    protected $fillable = ['rh_id', 'collaborateur_id', 'last_message_at'];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function rh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rh_id');
    }

    public function collaborateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collaborateur_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Retourne l'autre participant vu depuis $userId.
     */
    public function otherParticipant(int $userId): User
    {
        return $this->rh_id === $userId ? $this->collaborateur : $this->rh;
    }

    /**
     * Nombre de messages non lus pour $userId.
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Trouve ou crée une conversation entre un RH et un collaborateur.
     */
    public static function findOrCreateBetween(int $rhId, int $collaborateurId): self
    {
        return self::firstOrCreate(
            ['rh_id' => $rhId, 'collaborateur_id' => $collaborateurId],
            ['last_message_at' => now()]
        );
    }
}