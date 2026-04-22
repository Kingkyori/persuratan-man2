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
        'reference_number',
        'status',
        'notes',
        'google_drive_link',
        'file_name',
        'user_id'
    ];

    // Agar reception_date otomatis jadi objek Carbon (bisa pakai ->format())
    protected $casts = [
        'reception_date' => 'datetime',
    ];
}