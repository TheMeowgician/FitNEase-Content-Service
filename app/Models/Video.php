<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    protected $primaryKey = 'video_id';
    public $timestamps = false;

    protected $dates = ['created_at'];

    protected $fillable = [
        'exercise_id',
        'video_title',
        'video_url',
        'video_description',
        'duration_seconds',
        'video_type',
        'thumbnail_url',
        'video_quality',
        'file_size_mb',
        'is_active',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'file_size_mb' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'exercise_id');
    }
}
