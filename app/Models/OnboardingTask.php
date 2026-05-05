<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'deadline'        => 'date',
        'completion_date' => 'date',
    ];

    // ── Relations ─────────────────────────────────────────

    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class);
    }

    // ── Helpers ───────────────────────────────────────────

    public function isTermine(): bool
    {
        return $this->status === 'termine';
    }

    public function isEnCours(): bool
    {
        return $this->status === 'en_cours';
    }
}