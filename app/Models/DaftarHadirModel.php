<?php

namespace App\Models;

use CodeIgniter\Model;

class DaftarHadirModel extends Model
{
    public const STATUS = ['hadir', 'izin', 'tidak_hadir'];

    public const STATUS_LABELS = [
        'hadir'       => 'Hadir',
        'izin'        => 'Izin',
        'tidak_hadir' => 'Tidak Hadir',
    ];

    protected $table            = 'daftar_hadir';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['undangan_id', 'nama', 'jabatan', 'status', 'keterangan'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByUndangan(int $undanganId): array
    {
        return $this->where('undangan_id', $undanganId)->orderBy('nama', 'ASC')->findAll();
    }

    /** Aturan validasi untuk Validation::validateData(). */
    public function rules(): array
    {
        return [
            'nama' => [
                'label'  => 'Nama',
                'rules'  => 'required|max_length[100]',
                'errors' => ['required' => 'Nama peserta wajib diisi.', 'max_length' => 'Nama maksimal 100 karakter.'],
            ],
            'jabatan' => [
                'label'  => 'Jabatan',
                'rules'  => 'permit_empty|max_length[100]',
                'errors' => ['max_length' => 'Jabatan maksimal 100 karakter.'],
            ],
            'status' => [
                'label'  => 'Status',
                'rules'  => 'required|in_list[' . implode(',', self::STATUS) . ']',
                'errors' => ['required' => 'Status kehadiran wajib dipilih.', 'in_list' => 'Status kehadiran tidak valid.'],
            ],
            'keterangan' => [
                'label'  => 'Keterangan',
                'rules'  => 'permit_empty|max_length[255]',
                'errors' => ['max_length' => 'Keterangan maksimal 255 karakter.'],
            ],
        ];
    }
}
