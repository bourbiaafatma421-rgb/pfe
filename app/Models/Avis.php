<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avis extends Model
{
    protected $table = 'avis';

    protected $fillable = [
        'onboarding_id',
        'collaborateur_id',
        'etoiles',
        'commentaire',
        'rythme',
        'score_sante',
        'duree_jours',
        'valide_par',
        'eligible',
        'envoye_ia',
    ];

    protected $casts = [
        'eligible'  => 'boolean',
        'envoye_ia' => 'boolean',
        'etoiles'   => 'integer',
        'score_sante' => 'integer',
        'duree_jours' => 'integer',
    ];

    // =========================================================
    // Relations
    // =========================================================

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(Onboarding::class);
    }

    public function collaborateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collaborateur_id');
    }

    // =========================================================
    // Accessors
    // =========================================================

    // Retourne le label du rythme en français
    public function getRythmeLibelleAttribute(): string
    {
        return match($this->rythme) {
            'bon'   => 'Bon rythme',
            'moyen' => 'Rythme moyen',
            'lent'  => 'Rythme lent',
            default => $this->rythme,
        };
    }

    // Retourne le niveau du score (bien / moyen / faible)
    public function getNiveauScoreAttribute(): string
    {
        return match(true) {
            $this->score_sante >= 75 => 'bien',
            $this->score_sante >= 50 => 'moyen',
            default                  => 'faible',
        };
    }
}