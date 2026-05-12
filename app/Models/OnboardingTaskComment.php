<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingTaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'onboarding_task_id',
        'user_id',
        'content',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'link',
    ];

    // ── Relations ─────────────────────────────────────────

    public function task(): BelongsTo
    {
        return $this->belongsTo(OnboardingTask::class, 'onboarding_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────

    public function hasAttachment(): bool
    {
        return !is_null($this->attachment_path);
    }

    public function hasLink(): bool
    {
        return !is_null($this->link);
    }
}