<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Exercise extends Model
{
    protected $primaryKey = 'exercise_id';

    protected $fillable = [
        'exercise_name',
        'description',
        'difficulty_level',
        'target_muscle_group',
        'default_duration_seconds',
        'default_rest_duration_seconds',
        'instructions',
        'safety_tips',
        'calories_burned_per_minute',
        'equipment_needed',
        'exercise_category',
        'demo_gif_url',
    ];

    protected $casts = [
        'difficulty_level' => 'string',  // Keep as string to preserve ENUM values
        'default_duration_seconds' => 'integer',
        'default_rest_duration_seconds' => 'integer',
        'calories_burned_per_minute' => 'decimal:2',
    ];

    public function workouts(): BelongsToMany
    {
        return $this->belongsToMany(Workout::class, 'workout_exercises', 'exercise_id', 'workout_id')
            ->withPivot(['order_sequence', 'custom_duration_seconds', 'custom_rest_duration_seconds', 'sets_count']);
    }

    public function muscleGroups(): BelongsToMany
    {
        return $this->belongsToMany(MuscleGroup::class, 'exercise_muscle_groups', 'exercise_id', 'muscle_group_id')
            ->withPivot(['primary_target', 'activation_percentage']);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'exercise_id', 'exercise_id');
    }

    public function instructions(): HasMany
    {
        return $this->hasMany(ExerciseInstruction::class, 'exercise_id', 'exercise_id');
    }

    public function workoutExercises(): HasMany
    {
        return $this->hasMany(WorkoutExercise::class, 'exercise_id', 'exercise_id');
    }
}
