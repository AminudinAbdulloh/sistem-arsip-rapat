<?php

namespace App\Models;

use CodeIgniter\Model;

class NotulensiRapatModel extends Model
{
    public const STATUS = ['menunggu', 'terverifikasi', 'ditolak'];

    public const STATUS_LABELS = [
        'menunggu'      => 'Menunggu Verifikasi',
        'terverifikasi' => 'Terverifikasi',
        'ditolak'       => 'Ditolak',
    ];

    protected $table           = 'notulensi_rapat';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['undangan_id', 'deskripsi_rapat', 'catatan', 'dokumentasi', 'created_by',
                                   'status_verifikasi', 'catatan_verifikasi', 'verified_by', 'verified_at'];

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

    public function findAllWithRelations(?string $status = null): array
    {
        $builder = $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.hari, undangan_rapat.waktu as waktu_undangan, undangan_rapat.tempat, users.nama as created_by_nama')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->join('users', 'users.id = notulensi_rapat.created_by');

        if ($status !== null && in_array($status, self::STATUS, true)) {
            $builder->where('notulensi_rapat.status_verifikasi', $status);
        }

        return $builder->orderBy('undangan_rapat.waktu', 'DESC')->findAll();
    }

    public function findByIdWithRelations(int $id): ?array
    {
        return $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.hari, undangan_rapat.waktu as waktu_undangan, undangan_rapat.tempat, users.nama as created_by_nama, verifier.nama as verified_by_nama')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->join('users', 'users.id = notulensi_rapat.created_by')
                    ->join('users verifier', 'verifier.id = notulensi_rapat.verified_by', 'left')
                    ->where('notulensi_rapat.id', $id)
                    ->first();
    }

    public function countByStatus(string $status): int
    {
        return $this->where('status_verifikasi', $status)->countAllResults();
    }

    /**
     * Simpan keputusan verifikasi. $catatan kosong disimpan sebagai NULL,
     * sehingga persetujuan menghapus alasan penolakan sebelumnya.
     */
    public function setVerifikasi(int $id, string $status, int $verifierId, ?string $catatan): bool
    {
        return $this->update($id, [
            'status_verifikasi'  => $status,
            'catatan_verifikasi' => ($catatan === null || $catatan === '') ? null : $catatan,
            'verified_by'        => $verifierId,
            'verified_at'        => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Kolom verifikasi yang dikembalikan ke "menunggu"; digabung ke data update
     * saat isi notulensi diubah.
     *
     * @return array<string, mixed>
     */
    public function resetVerifikasiFields(): array
    {
        return [
            'status_verifikasi'  => 'menunggu',
            'catatan_verifikasi' => null,
            'verified_by'        => null,
            'verified_at'        => null,
        ];
    }

    /**
     * Arsip = notulensi terverifikasi. Filter: q (acara, tempat, deskripsi, catatan),
     * dari / sampai (Y-m-d, tanggal undangan).
     *
     * @param array{q?: string, dari?: string, sampai?: string} $filters
     */
    public function searchVerified(array $filters = []): array
    {
        $builder = $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.hari, undangan_rapat.waktu as waktu_undangan, undangan_rapat.tempat')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->where('notulensi_rapat.status_verifikasi', 'terverifikasi');

        $q = trim($filters['q'] ?? '');
        if ($q !== '') {
            // Query Builder tidak meng-escape wildcard LIKE pada nilai; lakukan manual
            // (memakai karakter ESCAPE koneksi) agar % dan _ dicari apa adanya.
            $esc = $this->db->likeEscapeChar;
            $q   = str_replace([$esc, '%', '_'], [$esc . $esc, $esc . '%', $esc . '_'], $q);

            $builder->groupStart()
                        ->like('undangan_rapat.acara', $q, 'both', true)
                        ->orLike('undangan_rapat.tempat', $q, 'both', true)
                        ->orLike('notulensi_rapat.deskripsi_rapat', $q, 'both', true)
                        ->orLike('notulensi_rapat.catatan', $q, 'both', true)
                    ->groupEnd();
        }
        if (! empty($filters['dari'])) {
            $builder->where('DATE(undangan_rapat.waktu) >=', $filters['dari']);
        }
        if (! empty($filters['sampai'])) {
            $builder->where('DATE(undangan_rapat.waktu) <=', $filters['sampai']);
        }

        return $builder->orderBy('undangan_rapat.waktu', 'DESC')->findAll();
    }

    public function findVerifiedById(int $id): ?array
    {
        return $this->select('notulensi_rapat.*, undangan_rapat.acara as nama_undangan, undangan_rapat.hari, undangan_rapat.waktu as waktu_undangan, undangan_rapat.tempat, verifier.nama as verified_by_nama')
                    ->join('undangan_rapat', 'undangan_rapat.id = notulensi_rapat.undangan_id')
                    ->join('users verifier', 'verifier.id = notulensi_rapat.verified_by', 'left')
                    ->where('notulensi_rapat.id', $id)
                    ->where('notulensi_rapat.status_verifikasi', 'terverifikasi')
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
