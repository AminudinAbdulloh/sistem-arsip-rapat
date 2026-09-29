<?php

namespace App\Models;

use CodeIgniter\Model;

class BeritaAcaraModel extends Model
{
    protected $table            = 'berita_acara';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['undangan_id', 'nomor', 'uraian', 'keputusan', 'created_by'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByUndangan(int $undanganId): ?array
    {
        return $this->where('undangan_id', $undanganId)->first();
    }

    /**
     * Buat berita acara, atau perbarui bila undangan ini sudah punya.
     * created_by tetap milik pembuat pertama.
     *
     * @param array{nomor: string, uraian: string, keputusan: string} $data
     */
    public function simpan(int $undanganId, array $data, int $userId): void
    {
        $existing = $this->findByUndangan($undanganId);
        $fields   = [
            'nomor'     => $data['nomor'] !== '' ? $data['nomor'] : null,
            'uraian'    => $data['uraian'],
            'keputusan' => $data['keputusan'] !== '' ? $data['keputusan'] : null,
        ];

        if ($existing) {
            $this->update($existing['id'], $fields);

            return;
        }

        $this->insert($fields + ['undangan_id' => $undanganId, 'created_by' => $userId]);
    }

    /** Aturan validasi untuk Validation::validateData(). */
    public function rules(): array
    {
        return [
            'nomor' => [
                'label'  => 'Nomor',
                'rules'  => 'permit_empty|max_length[100]',
                'errors' => ['max_length' => 'Nomor berita acara maksimal 100 karakter.'],
            ],
            'uraian' => [
                'label'  => 'Uraian',
                'rules'  => 'required',
                'errors' => ['required' => 'Uraian berita acara wajib diisi.'],
            ],
            'keputusan' => [
                'label' => 'Keputusan',
                'rules' => 'permit_empty',
            ],
        ];
    }
}
