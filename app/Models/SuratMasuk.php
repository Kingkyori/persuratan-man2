<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    use HasFactory;

    protected $table = 'surat_masuk';

    protected $fillable = [
        'origin',
        'reception_date',
        'letter_number',
        'subject',
        'department_destination',
        'reference_number',
        'status',
        'department_status',
        'notes',
        'department_notes',
        'google_drive_link',
        'local_file_path',
        'file_name',
        'share_token',
        'user_id'
    ];

    // Agar reception_date otomatis jadi objek Carbon (bisa pakai ->format())
    protected $casts = [
        'reception_date' => 'datetime',
    ];
}
