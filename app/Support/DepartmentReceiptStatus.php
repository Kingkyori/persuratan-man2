<?php

namespace App\Support;

class DepartmentReceiptStatus
{
    public const DEFAULT = 'pending';

    private const OPTIONS = [
        'pending' => [
            'label' => 'Belum Diterima',
            'class' => 'badge-department-pending',
            'description' => 'Departemen tujuan belum memberikan konfirmasi penerimaan surat.',
        ],
        'received' => [
            'label' => 'Sudah Diterima',
            'class' => 'badge-department-received',
            'description' => 'Departemen tujuan sudah menerima dan meninjau surat ini.',
        ],
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
        if (!$status || !isset(self::OPTIONS[$status])) {
            return self::DEFAULT;
        }

        return $status;
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
}
