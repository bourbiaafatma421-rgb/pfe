<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onboarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'generated_by',
        'status',
        'validated_by',
        'validated_at',
        'validation_notes',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    // ── Relations ─────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tasks()
    {
        return $this->hasMany(OnboardingTask::class);
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ── Helpers ───────────────────────────────────────────

    public function tasksByMonth(int $month)
    {
        return $this->tasks()->where('month_number', $month)->get();
    }

    public function tasksByWeek(int $month, int $week)
    {
        return $this->tasks()
            ->where('month_number', $month)
            ->where('week_number', $week)
            ->get();
    }

    public function progression(): int
    {
        $total   = $this->tasks()->count();
        $termine = $this->tasks()->where('status', 'termine')->count();

        if ($total === 0) return 0;
        return (int) round(($termine / $total) * 100);
    }

    public function isValide(): bool
    {
        return $this->status === 'valide';
    }

    public function isGenere(): bool
    {
        return $this->status === 'genere';
    }

    // ── Validation par RH ─────────────────────────────────

    public function valider(User $rh, ?string $notes = null): void
    {
        $this->update([
            'status'           => 'valide',
            'validated_by'     => $rh->id,
            'validated_at'     => now(),
            'validation_notes' => $notes,
        ]);
    }
}