<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_error',
        'log_import_id'
    ];
}
