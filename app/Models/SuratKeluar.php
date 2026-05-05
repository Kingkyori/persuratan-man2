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
        'entry_type',
        'document_type',
        'letter_date',
        'letter_number',
        'subject',
        'status',
        'notes',
        'generated_payload',
        'google_drive_link',
        'local_file_path',
        'generated_docx_path',
        'generated_docx_name',
        'file_name',
        'share_token',
        'user_id',
    ];

    protected $casts = [
        'letter_date' => 'datetime',
        'generated_payload' => 'array',
    ];
}
