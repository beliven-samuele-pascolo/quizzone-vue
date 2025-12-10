<?php

namespace App\Models;

use App\Enums\QuestionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => QuestionStatus::class,
            'timer_ends_at' => 'datetime',
        ];
    }

    public function buzzedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buzzed_user_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }
}
