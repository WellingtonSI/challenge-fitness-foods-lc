<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_import',
        'memory_usage_in_mb',
        'online_time_in_seconds',
        'status'
    ];
}
