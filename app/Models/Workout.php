<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workout extends Model
{
    protected $primaryKey = 'workout_id';

    protected $fillable = [
        'workout_name',
        'description',
        'total_duration_minutes',
        'difficulty_level',
        'target_muscle_groups',
        'workout_type',
        'created_by',
        'is_public',
        'is_system_generated',
        'total_exercises',
        'estimated_calories_burned',
    ];

    protected $casts = [
        'total_duration_minutes' => 'integer',
        'is_public' => 'boolean',
        'is_system_generated' => 'boolean',
        'total_exercises' => 'integer',
        'estimated_calories_burned' => 'decimal:2',
    ];

    public function setTargetMuscleGroupsAttribute($value)
    {
        $this->attributes['target_muscle_groups'] = is_array($value) ? implode(',', $value) : $value;
    }

    public function getTargetMuscleGroupsAttribute($value)
    {
        return $value ? explode(',', $value) : [];
    }

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'workout_exercises', 'workout_id', 'exercise_id')
            ->withPivot(['order_sequence', 'custom_duration_seconds', 'custom_rest_duration_seconds', 'sets_count'])
            ->orderByPivot('order_sequence');
    }

    public function workoutExercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class, 'workout_id', 'workout_id')
            ->orderBy('order_sequence');
    }
}
