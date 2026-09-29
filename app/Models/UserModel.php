<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    public const ROLES = ['admin', 'kaprodi', 'sekretaris', 'dosen'];

    public const ROLE_LABELS = [
        'admin'      => 'Admin',
        'kaprodi'    => 'Ketua Program Studi',
        'sekretaris' => 'Sekretaris/Staff',
        'dosen'      => 'Dosen',
    ];

    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nip', 'nama', 'kata_sandi', 'foto_profil', 'jabatan', 'role'];

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

    public function findByNip(string $nip): ?array
    {
        return $this->where('nip', $nip)->first();
    }

    public function updateFotoProfil(int $id, string $foto): bool
    {
        return $this->update($id, ['foto_profil' => $foto]);
    }

    public function countByRole(string $role): int
    {
        return $this->where('role', $role)->countAllResults();
    }

    public function hasRelatedData(int $id): bool
    {
        return $this->db->table('undangan_rapat')->where('created_by', $id)->countAllResults() > 0
            || $this->db->table('notulensi_rapat')->where('created_by', $id)->countAllResults() > 0;
    }

    /**
     * Aturan validasi form pengguna untuk Validation::validateData().
     *
     * @param int|null $ignoreId id pengguna yang sedang diedit (dikecualikan dari cek NIP unik)
     */
    public function rulesFor(?int $ignoreId, bool $passwordRequired): array
    {
        $uniqueNip = 'is_unique[users.nip' . ($ignoreId !== null ? ',id,' . $ignoreId : '') . ']';

        return [
            'nip' => [
                'label'  => 'NIP',
                'rules'  => "required|max_length[20]|{$uniqueNip}",
                'errors' => [
                    'required'   => 'NIP wajib diisi.',
                    'max_length' => 'NIP maksimal 20 karakter.',
                    'is_unique'  => 'NIP sudah terdaftar.',
                ],
            ],
            'nama' => [
                'label'  => 'Nama',
                'rules'  => 'required|max_length[100]',
                'errors' => [
                    'required'   => 'Nama wajib diisi.',
                    'max_length' => 'Nama maksimal 100 karakter.',
                ],
            ],
            'jabatan' => [
                'label'  => 'Jabatan',
                'rules'  => 'permit_empty|max_length[100]',
                'errors' => ['max_length' => 'Jabatan maksimal 100 karakter.'],
            ],
            'role' => [
                'label'  => 'Role',
                'rules'  => 'required|in_list[' . implode(',', self::ROLES) . ']',
                'errors' => [
                    'required' => 'Role wajib dipilih.',
                    'in_list'  => 'Role tidak valid.',
                ],
            ],
            'kata_sandi' => [
                'label'  => 'Kata sandi',
                'rules'  => ($passwordRequired ? 'required' : 'permit_empty') . '|min_length[8]',
                'errors' => [
                    'required'   => 'Kata sandi wajib diisi.',
                    'min_length' => 'Kata sandi minimal 8 karakter.',
                ],
            ],
        ];
    }
}
