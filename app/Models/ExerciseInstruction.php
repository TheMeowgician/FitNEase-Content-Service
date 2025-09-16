<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseInstruction extends Model
{
    protected $primaryKey = 'instruction_id';

    protected $fillable = [
        'exercise_id',
        'instruction_type',
        'instruction_text',
        'step_order',
        'is_critical',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'is_critical' => 'boolean',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'exercise_id');
    }
}
