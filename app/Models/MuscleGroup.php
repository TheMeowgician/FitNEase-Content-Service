<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MuscleGroup extends Model
{
    protected $primaryKey = 'muscle_group_id';

    protected $fillable = [
        'group_name',
        'description',
        'primary_muscles',
        'secondary_muscles',
        'exercise_benefits',
    ];

    public function exercises(): BelongsToMany
    {
        return $this->belongsToMany(Exercise::class, 'exercise_muscle_groups', 'muscle_group_id', 'exercise_id')
            ->withPivot(['primary_target', 'activation_percentage']);
    }
}
