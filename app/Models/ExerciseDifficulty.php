<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseDifficulty extends Model
{
    protected $primaryKey = 'difficulty_id';

    protected $fillable = [
        'difficulty_name',
        'description',
        'min_experience_months',
        'recommended_fitness_level',
        'intensity_scale',
    ];

    protected $casts = [
        'min_experience_months' => 'integer',
        'intensity_scale' => 'decimal:2',
    ];
}
