<?php

namespace App\Models;

use CodeIgniter\Model;

class NotulensiRapatModel extends Model
{
    protected $table            = 'notulensi_rapat';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['undangan_id', 'deskripsi_rapat', 'catatan', 'dokumentasi', 'created_by'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function findAllWithRelations(): array
    {
        return $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.hari, undangan_rapat.waktu as waktu_undangan, undangan_rapat.tempat, users.nama as created_by_nama')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->join('users', 'users.id = notulensi_rapat.created_by')
                    ->orderBy('notulensi_rapat.tgl_rapat', 'DESC')
                    ->findAll();
    }

    public function findByIdWithRelations(int $id): ?array
    {
        return $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.hari, undangan_rapat.waktu as waktu_undangan, undangan_rapat.tempat, users.nama as created_by_nama')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->join('users', 'users.id = notulensi_rapat.created_by')
                    ->where('notulensi_rapat.id', $id)
                    ->first();
    }

    public function countByMonth(int $month, int $year): int
    {
        return $this->where('MONTH(tgl_rapat)', $month)
                    ->where('YEAR(tgl_rapat)', $year)
                    ->countAllResults();
    }

    public function countByYear(int $year): int
    {
        return $this->where('YEAR(tgl_rapat)', $year)
                    ->countAllResults();
    }

    public function findByMonth(int $month, int $year): array
    {
        return $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.tempat, users.nama as created_by_nama')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->join('users', 'users.id = notulensi_rapat.created_by')
                    ->where('MONTH(notulensi_rapat.tgl_rapat)', $month)
                    ->where('YEAR(notulensi_rapat.tgl_rapat)', $year)
                    ->orderBy('notulensi_rapat.tgl_rapat', 'ASC')
                    ->findAll();
    }

    public function findByYear(int $year): array
    {
        return $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.tempat, users.nama as created_by_nama')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->join('users', 'users.id = notulensi_rapat.created_by')
                    ->where('YEAR(notulensi_rapat.tgl_rapat)', $year)
                    ->orderBy('notulensi_rapat.tgl_rapat', 'ASC')
                    ->findAll();
    }
}
