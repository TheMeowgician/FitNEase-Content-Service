<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutExercise extends Model
{
    protected $primaryKey = 'workout_exercise_id';
    public $timestamps = false;

    protected $fillable = [
        'workout_id',
        'exercise_id',
        'order_sequence',
        'custom_duration_seconds',
        'custom_rest_duration_seconds',
        'sets_count',
    ];

    protected $casts = [
        'order_sequence' => 'integer',
        'custom_duration_seconds' => 'integer',
        'custom_rest_duration_seconds' => 'integer',
        'sets_count' => 'integer',
    ];

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class, 'workout_id', 'workout_id');
    }

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'exercise_id');
    }
}
