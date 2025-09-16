<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $primaryKey = 'file_id';
    public $timestamps = false;

    protected $dates = ['uploaded_at'];

    protected $fillable = [
        'file_name',
        'original_file_name',
        'file_path',
        'file_type',
        'file_size_bytes',
        'mime_type',
        'uploaded_by',
        'entity_type',
        'entity_id',
        'is_public',
        'is_active',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'entity_id' => 'integer',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'uploaded_at' => 'datetime',
    ];
}
