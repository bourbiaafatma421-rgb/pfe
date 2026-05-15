<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'onboarding_id',
        'month_number',
        'week_number',
        'day_name',
        'task_title',
        'objective',
        'type',
        'deadline',
        'completion_date',
        'status',
        'rejection_reason',
        'responsable_id',
    ];

    protected $casts = [
        'deadline'        => 'date',
        'completion_date' => 'date',
    ];

    // ── Statuts autorisés ──────────────────────────────────────

    public const STATUSES = [
        'en_attente',
        'en_cours',
        'en_validation',
        'rejetee',
        'termine',
    ];

    // ── Relations ──────────────────────────────────────────────

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(Onboarding::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OnboardingTaskComment::class, 'onboarding_task_id')->latest();
    }
    public function responsable(): BelongsTo{
        return $this->belongsTo(User::class, 'responsable_id');
}   

    // ── Helpers ────────────────────────────────────────────────

    public function isTermine(): bool
    {
        return $this->status === 'termine';
    }

    public function isEnCours(): bool
    {
        return $this->status === 'en_cours';
    }

    public function isPendingValidation(): bool
    {
        return $this->status === 'en_validation';
    }

    public function isRejetee(): bool
    {
        return $this->status === 'rejetee';
    }
}