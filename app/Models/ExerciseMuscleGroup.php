<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseMuscleGroup extends Model
{
    protected $primaryKey = 'exercise_muscle_id';
    public $timestamps = false;

    protected $fillable = [
        'exercise_id',
        'muscle_group_id',
        'primary_target',
        'activation_percentage',
    ];

    protected $casts = [
        'primary_target' => 'boolean',
        'activation_percentage' => 'decimal:2',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'exercise_id');
    }

    public function muscleGroup(): BelongsTo
    {
        return $this->belongsTo(MuscleGroup::class, 'muscle_group_id', 'muscle_group_id');
    }
}
