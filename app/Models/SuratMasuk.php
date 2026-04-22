<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'user_id',
    ];

    protected $casts = [
        'reception_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Get pending letters
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get done letters
     */
    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope: Get disposed letters
     */
    public function scopeDisposed($query)
    {
        return $query->where('status', 'disposed');
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">⏳ Pending</span>',
            'done' => '<span class="badge badge-success">✓ Done</span>',
            'disposed' => '<span class="badge badge-danger">✗ Disposed</span>',
        ];
        return $badges[$this->status] ?? '';
    }
}
