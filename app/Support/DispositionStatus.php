<?php

namespace App\Support;

class DispositionStatus
{
    public const DEFAULT = 'draft';

    private const OPTIONS = [
        'draft' => [
            'label' => 'Draft',
            'class' => 'badge-draft',
            'description' => 'Surat masih berupa konsep awal dan belum diajukan.',
        ],
        'pending_approval' => [
            'label' => 'Menunggu Persetujuan',
            'class' => 'badge-review',
            'description' => 'Surat sedang menunggu persetujuan pimpinan atau pejabat terkait.',
        ],
        'revision' => [
            'label' => 'Revisi',
            'class' => 'badge-revision',
            'description' => 'Surat perlu diperbaiki sebelum lanjut ke tahap berikutnya.',
        ],
        'rejected' => [
            'label' => 'Ditolak',
            'class' => 'badge-rejected',
            'description' => 'Surat tidak disetujui pada proses disposisi.',
        ],
        'approved' => [
            'label' => 'Disetujui',
            'class' => 'badge-approved',
            'description' => 'Surat sudah selesai diproses dan disetujui.',
        ],
    ];

    private const LEGACY_MAP = [
        'pending' => 'pending_approval',
        'done' => 'approved',
        'disposed' => 'approved',
        'review' => 'pending_approval',
        'revisi' => 'revision',
        'final' => 'approved',
        'aktif' => 'approved',
        'selesai' => 'approved',
    ];

    public static function options(): array
    {
        return self::OPTIONS;
    }

    public static function keys(): array
    {
        return array_keys(self::OPTIONS);
    }

    public static function normalize(?string $status): string
    {
        if (!$status) {
            return self::DEFAULT;
        }

        if (isset(self::OPTIONS[$status])) {
            return $status;
        }

        return self::LEGACY_MAP[$status] ?? self::DEFAULT;
    }

    public static function meta(?string $status): array
    {
        $key = self::normalize($status);

        return self::OPTIONS[$key];
    }

    public static function label(?string $status): string
    {
        return self::meta($status)['label'];
    }

    public static function description(?string $status): string
    {
        return self::meta($status)['description'];
    }

    public static function databaseValuesFor(string $normalizedStatus): array
    {
        $values = [$normalizedStatus];

        foreach (self::LEGACY_MAP as $legacyStatus => $mappedStatus) {
            if ($mappedStatus === $normalizedStatus) {
                $values[] = $legacyStatus;
            }
        }

        return array_values(array_unique($values));
    }

    public static function previewUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $fileId = self::extractDriveFileId($url);

        if ($fileId) {
            return "https://drive.google.com/file/d/{$fileId}/preview";
        }

        if (str_contains($url, 'drive.google.com/drive/folders/')) {
            return null;
        }

        return $url;
    }

    public static function downloadUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $fileId = self::extractDriveFileId($url);

        if ($fileId) {
            return "https://drive.google.com/uc?export=download&id={$fileId}";
        }

        if (str_contains($url, 'drive.google.com/drive/folders/')) {
            return null;
        }

        return $url;
    }

    private static function extractDriveFileId(string $url): ?string
    {
        if (preg_match('~drive\.google\.com/file/d/([^/]+)~', $url, $matches)) {
            return $matches[1];
        }

        if (str_contains($url, 'drive.google.com') && preg_match('~[?&]id=([^&]+)~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
