<?php

namespace App\Models;

use CodeIgniter\Model;

class DokumenRapatModel extends Model
{
    protected $table            = 'dokumen_rapat';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['undangan_id', 'judul', 'berkas', 'nama_asli', 'ukuran', 'mime', 'uploaded_by'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByUndangan(int $undanganId): array
    {
        return $this->select('dokumen_rapat.*, users.nama as uploaded_by_nama')
                    ->join('users', 'users.id = dokumen_rapat.uploaded_by')
                    ->where('dokumen_rapat.undangan_id', $undanganId)
                    ->orderBy('dokumen_rapat.created_at', 'DESC')
                    ->orderBy('dokumen_rapat.id', 'DESC')
                    ->findAll();
    }
}
