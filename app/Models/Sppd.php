<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sppd extends Model
{
    use HasFactory;

    protected $table = 'sppd';

    protected $fillable = [
        'employee_id',
        'employee_name',
        'destination',
        'departure_date',
        'duration_days',
        'purpose',
        'status',
        'notes',
        'google_drive_link',
        'file_name',
        'user_id',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
    ];
}
