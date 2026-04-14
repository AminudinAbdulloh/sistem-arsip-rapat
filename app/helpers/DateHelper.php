<?php

/**
 * DateHelper
 *
 * Kumpulan fungsi pemformatan tanggal dalam Bahasa Indonesia.
 * Digunakan oleh controller dan view agar tidak ada duplikasi logika.
 */
class DateHelper
{
    private const BULAN = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    private const HARI = [
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
        4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
    ];

    /**
     * Format: "12 Januari 2025"
     */
    public static function tanggal(string $dateStr): string
    {
        $ts = strtotime($dateStr);
        return date('j', $ts) . ' ' . self::BULAN[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }

    /**
     * Format: "Senin, 12 Januari 2025"
     */
    public static function tanggalLengkap(string $dateStr): string
    {
        $ts = strtotime($dateStr);
        return self::HARI[(int) date('N', $ts)] . ', ' . self::tanggal($dateStr);
    }

    /**
     * Format: "Senin / 12 Januari 2025" (untuk surat undangan)
     */
    public static function tanggalSurat(string $dateStr): string
    {
        $ts = strtotime($dateStr);
        return self::HARI[(int) date('N', $ts)] . ' / ' . self::tanggal($dateStr);
    }

    /**
     * Format: "12.30 WIB"
     */
    public static function jam(string $dateTimeStr): string
    {
        return date('H.i', strtotime($dateTimeStr)) . ' WIB';
    }

    /**
     * Format: "12.30 WIB - Selesai"
     */
    public static function jamSelesai(string $dateTimeStr): string
    {
        return self::jam($dateTimeStr) . ' - Selesai';
    }

    /**
     * Kembalikan array nama bulan (index 1–12).
     */
    public static function listBulan(): array
    {
        return self::BULAN;
    }
}