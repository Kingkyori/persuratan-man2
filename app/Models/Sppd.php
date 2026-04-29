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
        'entry_type',
        'document_number',
        'destination',
        'departure_date',
        'document_date',
        'duration_days',
        'purpose',
        'status',
        'notes',
        'assignment_payload',
        'google_drive_link',
        'local_file_path',
        'generated_docx_path',
        'generated_docx_name',
        'file_name',
        'user_id',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'document_date' => 'date',
        'assignment_payload' => 'array',
    ];
}
