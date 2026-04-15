<?php
require_once BASE_PATH . '/app/models/Model.php';

class User extends Model
{
    protected string $table = 'users';

    public function findByNip(string $nip): ?array
    {
        return $this->fetchOne("SELECT * FROM `{$this->table}` WHERE nip = ?", [$nip]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne("SELECT * FROM `{$this->table}` WHERE id = ?", [$id]);
    }
}