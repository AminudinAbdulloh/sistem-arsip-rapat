<?php

namespace App\Models;

use CodeIgniter\Model;

class UndanganRapatModel extends Model
{
    protected $table            = 'undangan_rapat';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['hari', 'waktu', 'tempat', 'acara', 'created_by'];

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

    public function findAllWithUser(): array
    {
        return $this->select('undangan_rapat.*, users.nama as created_by_nama')
                    ->join('users', 'users.id = undangan_rapat.created_by')
                    ->orderBy('undangan_rapat.waktu', 'DESC')
                    ->findAll();
    }

    public function findByIdWithUser(int $id): ?array
    {
        return $this->select('undangan_rapat.*, users.nama as created_by_nama')
                    ->join('users', 'users.id = undangan_rapat.created_by')
                    ->where('undangan_rapat.id', $id)
                    ->first();
    }

    public function findByMonth(int $month, int $year): array
    {
        return $this->select('undangan_rapat.*, users.nama as created_by_nama, (SELECT COUNT(*) FROM notulensi_rapat WHERE notulensi_rapat.undangan_id = undangan_rapat.id) as has_notulensi')
                    ->join('users', 'users.id = undangan_rapat.created_by')
                    ->where('MONTH(undangan_rapat.waktu)', $month)
                    ->where('YEAR(undangan_rapat.waktu)', $year)
                    ->orderBy('undangan_rapat.waktu', 'ASC')
                    ->findAll();
    }

    public function findByYear(int $year): array
    {
        return $this->select('undangan_rapat.*, users.nama as created_by_nama, (SELECT COUNT(*) FROM notulensi_rapat WHERE notulensi_rapat.undangan_id = undangan_rapat.id) as has_notulensi')
                    ->join('users', 'users.id = undangan_rapat.created_by')
                    ->where('YEAR(undangan_rapat.waktu)', $year)
                    ->orderBy('undangan_rapat.waktu', 'ASC')
                    ->findAll();
    }

    public function countByMonth(int $month, int $year): int
    {
        return $this->where('MONTH(waktu)', $month)
                    ->where('YEAR(waktu)', $year)
                    ->countAllResults();
    }

    public function countByYear(int $year): int
    {
        return $this->where('YEAR(waktu)', $year)
                    ->countAllResults();
    }

    public function getAvailableYears(): array
    {
        $result = $this->select('YEAR(waktu) as year')
                       ->distinct()
                       ->orderBy('year', 'DESC')
                       ->findAll();
        return array_column($result, 'year');
    }

    public function hasNotulensi(int $id): bool
    {
        $db = \Config\Database::connect();
        $builder = $db->table('notulensi_rapat');
        return $builder->where('undangan_id', $id)->countAllResults() > 0;
    }
}
