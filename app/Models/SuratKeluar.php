<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluar';

    protected $fillable = [
        'destination',
        'letter_date',
        'letter_number',
        'subject',
        'status',
        'notes',
        'google_drive_link',
        'file_name',
        'user_id',
    ];

    protected $casts = [
        'letter_date' => 'datetime',
    ];
}
