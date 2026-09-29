# Fondasi Peran & Hak Akses Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan empat peran (admin, kaprodi, sekretaris, dosen), pembatasan akses per route, menu/tombol menurut role, dan manajemen pengguna oleh Admin.

**Architecture:** Kolom `users.role` (ENUM) disimpan di session saat login. Filter route `role:<daftar>` menjadi satu-satunya penjaga akses; helper `has_role()` hanya menyesuaikan tampilan. `UserController` (khusus admin) mengelola pengguna dengan pengaman admin terakhir / akun sendiri.

**Tech Stack:** PHP 8.2, CodeIgniter 4.7, MySQL (XAMPP), PHPUnit 10, Tailwind (CDN).

**Spec:** `docs/superpowers/specs/2026-09-30-role-based-access-design.md`

## Global Constraints

- Nilai role persis: `admin`, `kaprodi`, `sekretaris`, `dosen`. Default kolom: `dosen`. Akun lama saat migrasi: `admin`.
- Pesan akses ditolak persis: `Anda tidak memiliki akses ke halaman tersebut.` (redirect ke `/dashboard`); belum login: redirect `/login`.
- Kata sandi minimal 8 karakter, di-hash dengan `password_hash($x, PASSWORD_DEFAULT)`.
- Antarmuka berbahasa Indonesia, gaya Tailwind sama dengan view Undangan (warna utama `#1e3a5f`).
- Test dijalankan dengan `vendor/bin/phpunit` (butuh MySQL XAMPP aktif, DB `arsip_rapat_test`). Baseline sebelum perubahan: 29 test lulus.
- Tidak ada commit otomatis; commit hanya jika pengguna memintanya. Tiap task diakhiri checkpoint (menjalankan seluruh suite).

## Struktur File

| File | Aksi | Tanggung jawab |
|---|---|---|
| `app/Database/Migrations/2026-09-30-100000_AddRoleToUsers.php` | Buat | Kolom `role`, akun lama jadi admin |
| `app/Models/UserModel.php` | Ubah | Konstanta role, aturan validasi, `countByRole`, `hasRelatedData` |
| `app/Controllers/AuthController.php` | Ubah | Simpan `role` di session |
| `app/Database/Seeds/UserSeeder.php` | Ubah | 4 akun demo |
| `app/Filters/RoleFilter.php` | Buat | Penjaga akses per role |
| `app/Helpers/auth_helper.php` | Buat | `has_role()` untuk view |
| `app/Config/Filters.php`, `app/Config/Autoload.php` | Ubah | Alias `role`, load helper `auth` |
| `app/Config/Routes.php` | Ubah | Terapkan filter role, route `/users` |
| `app/Controllers/UserController.php` | Buat | CRUD pengguna + pengaman |
| `app/Views/Users/{index,create,edit}.php` | Buat | UI manajemen pengguna |
| `app/Views/Layouts/main.php`, `Undangan/index.php`, `Notulensi/index.php`, `Notulensi/show.php`, `Dashboard/index.php` | Ubah | Menu/tombol menurut role |
| `tests/unit/...` | Buat/Ubah | Lihat tiap task |
| `README.md` | Ubah | Dokumentasi role & akun demo |

---

### Task 1: Kolom role, model, login, seeder

**Files:**
- Create: `app/Database/Migrations/2026-09-30-100000_AddRoleToUsers.php`
- Modify: `app/Models/UserModel.php`, `app/Controllers/AuthController.php`, `app/Database/Seeds/UserSeeder.php`
- Test: `tests/unit/Models/UserModelTest.php`, `tests/unit/Controllers/AuthControllerTest.php`

**Interfaces:**
- Produces: `UserModel::ROLES` (`list<string>`), `UserModel::ROLE_LABELS` (`array<string,string>`), `UserModel::rulesFor(?int $ignoreId, bool $passwordRequired): array` (aturan berlabel untuk `validateData`), `UserModel::countByRole(string $role): int`, `UserModel::hasRelatedData(int $id): bool`; session `user` memuat kunci `role`.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan di `tests/unit/Models/UserModelTest.php` (di dalam class, setelah test yang ada):

```php
    public function testRoleDefaultDosenSaatTidakDiisi(): void
    {
        $user = (new UserModel())->findByNip('198001012005011001');

        $this->assertSame('dosen', $user['role']);
    }

    public function testCountByRoleMenghitungPerRole(): void
    {
        $this->db->table('users')->insert([
            'nip' => '111', 'nama' => 'A', 'kata_sandi' => 'x', 'role' => 'admin',
        ]);
        $model = new UserModel();

        $this->assertSame(1, $model->countByRole('admin'));
        $this->assertSame(1, $model->countByRole('dosen'));
        $this->assertSame(0, $model->countByRole('kaprodi'));
    }

    public function testRulesForMenolakRoleTidakValidDanKataSandiPendek(): void
    {
        $validation = service('validation');
        $validation->setRules((new UserModel())->rulesFor(null, true));

        $ok = $validation->run([
            'nip' => '222', 'nama' => 'B', 'jabatan' => '', 'role' => 'hacker', 'kata_sandi' => 'short',
        ]);

        $this->assertFalse($ok);
        $this->assertArrayHasKey('role', $validation->getErrors());
        $this->assertArrayHasKey('kata_sandi', $validation->getErrors());
    }

    public function testRulesForMenolakNipDuplikatKecualiMilikSendiri(): void
    {
        $model  = new UserModel();
        $ownId  = (int) $model->findByNip('198001012005011001')['id'];
        $input  = ['nip' => '198001012005011001', 'nama' => 'A', 'jabatan' => '', 'role' => 'dosen', 'kata_sandi' => ''];

        $validation = service('validation');
        $validation->setRules($model->rulesFor(null, false));
        $this->assertFalse($validation->run($input));
        $this->assertArrayHasKey('nip', $validation->getErrors());

        $validation->reset();
        $validation->setRules($model->rulesFor($ownId, false));
        $this->assertTrue($validation->run($input));
    }

    public function testHasRelatedDataTrueBilaPenggunaPunyaUndangan(): void
    {
        $model = new UserModel();
        $id    = (int) $model->findByNip('198001012005011001')['id'];
        $this->assertFalse($model->hasRelatedData($id));

        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $id,
        ]);

        $this->assertTrue($model->hasRelatedData($id));
    }

    public function testSeederMembuatSatuAkunPerRole(): void
    {
        $this->db->table('users')->where('nip', '198001012005011001')->delete();

        (new \App\Database\Seeds\UserSeeder(config('Database')))->run();

        $model = new UserModel();
        foreach (UserModel::ROLES as $role) {
            $this->assertSame(1, $model->countByRole($role), "role {$role}");
        }
    }
```

Tambahkan di `tests/unit/Controllers/AuthControllerTest.php` (dalam class):

```php
    public function testLoginMenyimpanRoleDiSession(): void
    {
        $this->db->table('users')->where('nip', '198001012005011001')->update(['role' => 'kaprodi']);

        $this->withSession()->post('/login', [
            'nip'        => '198001012005011001',
            'kata_sandi' => 'password',
        ]);

        $this->assertSame('kaprodi', session()->get('user')['role']);
    }
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `vendor/bin/phpunit tests/unit/Models/UserModelTest.php tests/unit/Controllers/AuthControllerTest.php`
Expected: FAIL (kolom `role` tidak ada / method tidak ada).

- [ ] **Step 3: Implementasi**

Buat `app/Database/Migrations/2026-09-30-100000_AddRoleToUsers.php`:

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role' => [
                'type'    => "ENUM('admin','kaprodi','sekretaris','dosen')",
                'null'    => false,
                'default' => 'dosen',
                'after'   => 'jabatan',
            ],
        ]);

        // Akun yang sudah ada dijadikan admin agar tidak ada yang terkunci.
        $this->db->table('users')->update(['role' => 'admin']);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role');
    }
}
```

Di `app/Models/UserModel.php`: ubah `allowedFields` menjadi `['nip', 'nama', 'kata_sandi', 'foto_profil', 'jabatan', 'role']`; tambahkan di awal class (sebelum `protected $table`):

```php
    public const ROLES = ['admin', 'kaprodi', 'sekretaris', 'dosen'];

    public const ROLE_LABELS = [
        'admin'      => 'Admin',
        'kaprodi'    => 'Ketua Program Studi',
        'sekretaris' => 'Sekretaris/Staff',
        'dosen'      => 'Dosen',
    ];

```

Tambahkan method di akhir class:

```php
    public function countByRole(string $role): int
    {
        return $this->where('role', $role)->countAllResults();
    }

    public function hasRelatedData(int $id): bool
    {
        $db = $this->db;

        return $db->table('undangan_rapat')->where('created_by', $id)->countAllResults() > 0
            || $db->table('notulensi_rapat')->where('created_by', $id)->countAllResults() > 0;
    }

    /**
     * Aturan validasi form pengguna untuk Validation::validateData().
     *
     * @param int|null $ignoreId  id pengguna yang sedang diedit (dikecualikan dari cek NIP unik)
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
```

Di `app/Controllers/AuthController.php`, tambahkan `'role' => $user['role'],` setelah baris `'jabatan' => $user['jabatan'],` pada `$sessionData`.

Ganti isi `app/Database/Seeds/UserSeeder.php` array `$data` menjadi:

```php
        $data = [
            [
                'nip' => '198001012005011001',
                'nama' => 'Administrator ITD',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Administrator',
                'role' => 'admin',
            ],
            [
                'nip' => '197505102003121001',
                'nama' => 'Dr. Budi Santoso',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Kepala Program Studi',
                'role' => 'kaprodi',
            ],
            [
                'nip' => '198502152010012002',
                'nama' => 'Dr. Siti Rahayu',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Sekretaris Prodi',
                'role' => 'sekretaris',
            ],
            [
                'nip' => '199003202019031003',
                'nama' => 'Ahmad Fauzi, M.Kom.',
                'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
                'jabatan' => 'Dosen',
                'role' => 'dosen',
            ],
        ];
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `vendor/bin/phpunit`
Expected: semua lulus (29 lama + 7 baru).

- [ ] **Step 5: Checkpoint** — suite penuh hijau; lanjut ke Task 2.

---

### Task 2: RoleFilter, helper, dan penerapan di route

**Files:**
- Create: `app/Filters/RoleFilter.php`, `app/Helpers/auth_helper.php`, `tests/unit/Filters/RoleFilterTest.php`
- Modify: `app/Config/Filters.php`, `app/Config/Autoload.php`, `app/Config/Routes.php`, `tests/unit/Controllers/UndanganControllerTest.php`, `tests/unit/Controllers/NotulensiControllerTest.php`

**Interfaces:**
- Consumes: session `user.role` dari Task 1.
- Produces: alias filter `role` (argumen = daftar role dipisah koma); helper global `has_role(string ...$roles): bool`.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/unit/Filters/RoleFilterTest.php`:

```php
<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pengujian matriks hak akses: role x route.
 *
 * @internal
 */
final class RoleFilterTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private const PESAN = 'Anda tidak memiliki akses ke halaman tersebut.';

    public static function matriksProvider(): array
    {
        return [
            // route tulis undangan (admin, sekretaris)
            'admin tulis undangan'      => ['admin', '/undangan/create', true],
            'sekretaris tulis undangan' => ['sekretaris', '/undangan/create', true],
            'kaprodi tulis undangan'    => ['kaprodi', '/undangan/create', false],
            'dosen tulis undangan'      => ['dosen', '/undangan/create', false],
            // route tulis notulensi
            'sekretaris tulis notulensi' => ['sekretaris', '/notulensi/create', true],
            'kaprodi tulis notulensi'    => ['kaprodi', '/notulensi/create', false],
            // route baca (admin, sekretaris, kaprodi)
            'kaprodi baca undangan'   => ['kaprodi', '/undangan', true],
            'dosen baca undangan'     => ['dosen', '/undangan', false],
            'kaprodi baca notulensi'  => ['kaprodi', '/notulensi', true],
            'dosen baca notulensi'    => ['dosen', '/notulensi', false],
            // laporan (admin, kaprodi)
            'admin laporan'       => ['admin', '/dashboard/download?type=tahunan&tahun=2026', true],
            'kaprodi laporan'     => ['kaprodi', '/dashboard/download?type=tahunan&tahun=2026', true],
            'sekretaris laporan'  => ['sekretaris', '/dashboard/download?type=tahunan&tahun=2026', false],
            'dosen laporan'       => ['dosen', '/dashboard/download?type=tahunan&tahun=2026', false],
            // dashboard: semua role
            'dosen dashboard'     => ['dosen', '/dashboard', true],
        ];
    }

    /**
     * @dataProvider matriksProvider
     */
    public function testMatriksAkses(string $role, string $uri, bool $diizinkan): void
    {
        $result = $this->withSession($this->sessionFor($role))->get($uri);

        if ($diizinkan) {
            $result->assertOK();
        } else {
            $result->assertRedirectTo('/dashboard');
            $result->assertSessionHas('error', self::PESAN);
        }
    }

    public function testBelumLoginDiarahkanKeLogin(): void
    {
        $result = $this->get('/undangan');

        $result->assertRedirectTo('/login');
    }

    public function testRouteTulisMenolakPostDariKaprodi(): void
    {
        $result = $this->withSession($this->sessionFor('kaprodi'))->post('/undangan/store', [
            'hari' => 'Senin', 'waktu' => '2026-10-05T09:00', 'tempat' => 'R', 'acara' => 'A',
        ]);

        $result->assertRedirectTo('/dashboard');
        $this->assertSame(0, $this->db->table('undangan_rapat')->countAllResults());
    }

    public function testHelperHasRole(): void
    {
        $this->withSession($this->sessionFor('sekretaris'));

        $this->assertTrue(has_role('admin', 'sekretaris'));
        $this->assertFalse(has_role('admin', 'kaprodi'));
    }

    private function sessionFor(string $role): array
    {
        return ['user' => ['id' => 1, 'nip' => '1', 'nama' => 'Tester', 'jabatan' => '', 'foto_profil' => null, 'role' => $role]];
    }
}
```

Di `tests/unit/Controllers/UndanganControllerTest.php` dan `NotulensiControllerTest.php`, pada array di `sessionLogin()` tambahkan `'role' => 'admin',` (setelah `'jabatan' => 'Kepala Program Studi',`).

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `vendor/bin/phpunit tests/unit/Filters`
Expected: FAIL (semua role masih boleh mengakses; helper belum ada).

- [ ] **Step 3: Implementasi**

Buat `app/Filters/RoleFilter.php`:

```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Pembatas akses per role. Pemakaian di route: ['filter' => 'role:admin,sekretaris'].
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session()->get('user');

        if (! $user) {
            return redirect()->to('/login');
        }

        if (! in_array($user['role'] ?? null, $arguments ?? [], true)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
```

Buat `app/Helpers/auth_helper.php`:

```php
<?php

if (! function_exists('has_role')) {
    /**
     * True bila role pengguna yang login termasuk salah satu $roles.
     * Hanya untuk menyesuaikan tampilan; penjagaan akses ada di RoleFilter.
     */
    function has_role(string ...$roles): bool
    {
        $role = session()->get('user')['role'] ?? null;

        return $role !== null && in_array($role, $roles, true);
    }
}
```

`app/Config/Filters.php`: tambahkan setelah alias `guest`: `'role'          => \App\Filters\RoleFilter::class,`.

`app/Config/Autoload.php`: ubah `public $helpers = [];` menjadi `public $helpers = ['auth'];`.

`app/Config/Routes.php`: ganti blok "Auth required routes" ke bawah dengan:

```php
// Auth required routes
$routes->get('/', 'DashboardController::index', ['filter' => 'auth']);
$routes->get('/dashboard', 'DashboardController::index', ['filter' => 'auth']);
$routes->get('/dashboard/download', 'DashboardController::downloadLaporan', ['filter' => 'role:admin,kaprodi']);

// Undangan routes (baca: admin, sekretaris, kaprodi; tulis: admin, sekretaris)
$routes->get('/undangan', 'UndanganController::index', ['filter' => 'role:admin,sekretaris,kaprodi']);
$routes->get('/undangan/create', 'UndanganController::create', ['filter' => 'role:admin,sekretaris']);
$routes->post('/undangan/store', 'UndanganController::store', ['filter' => 'role:admin,sekretaris']);
$routes->get('/undangan/(:num)/edit', 'UndanganController::edit/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/undangan/(:num)/update', 'UndanganController::update/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/undangan/(:num)/delete', 'UndanganController::delete/$1', ['filter' => 'role:admin,sekretaris']);
$routes->get('/undangan/(:num)/download', 'UndanganController::downloadPdf/$1', ['filter' => 'role:admin,sekretaris']);

// Notulensi routes (baca: admin, sekretaris, kaprodi; tulis: admin, sekretaris)
$routes->get('/notulensi', 'NotulensiController::index', ['filter' => 'role:admin,sekretaris,kaprodi']);
$routes->get('/notulensi/create', 'NotulensiController::create', ['filter' => 'role:admin,sekretaris']);
$routes->post('/notulensi/store', 'NotulensiController::store', ['filter' => 'role:admin,sekretaris']);
$routes->get('/notulensi/(:num)/show', 'NotulensiController::show/$1', ['filter' => 'role:admin,sekretaris,kaprodi']);
$routes->get('/notulensi/(:num)/edit', 'NotulensiController::edit/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/notulensi/(:num)/update', 'NotulensiController::update/$1', ['filter' => 'role:admin,sekretaris']);
$routes->post('/notulensi/(:num)/delete', 'NotulensiController::delete/$1', ['filter' => 'role:admin,sekretaris']);
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `vendor/bin/phpunit`
Expected: semua lulus. Jika test matriks gagal karena view (`assertOK` pada halaman yang gagal render), periksa error dan perbaiki penyebabnya, bukan test.

- [ ] **Step 5: Checkpoint** — suite penuh hijau; lanjut ke Task 3.

---

### Task 3: Manajemen pengguna (Admin)

**Files:**
- Create: `app/Controllers/UserController.php`, `app/Views/Users/index.php`, `app/Views/Users/create.php`, `app/Views/Users/edit.php`, `tests/unit/Controllers/UserControllerTest.php`
- Modify: `app/Config/Routes.php`

**Interfaces:**
- Consumes: `UserModel::ROLES`, `ROLE_LABELS`, `rulesFor`, `countByRole`, `hasRelatedData` (Task 1); filter `role:admin` (Task 2).
- Produces: route `/users` (GET index), `/users/create` (GET), `/users/store` (POST), `/users/(:num)/edit` (GET), `/users/(:num)/update` (POST), `/users/(:num)/delete` (POST).

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/unit/Controllers/UserControllerTest.php`:

```php
<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Pengujian manajemen pengguna oleh Admin, termasuk tiga pengaman:
 * tidak hapus/ubah role diri sendiri, tidak hapus/turunkan admin terakhir,
 * tidak hapus pengguna yang sudah punya data.
 *
 * @internal
 */
final class UserControllerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminId = $this->createUser('100', 'Admin Uji', 'admin');
    }

    private function createUser(string $nip, string $nama, string $role): int
    {
        $this->db->table('users')->insert([
            'nip' => $nip, 'nama' => $nama, 'jabatan' => 'X', 'role' => $role,
            'kata_sandi' => password_hash('password', PASSWORD_DEFAULT),
        ]);

        return (int) $this->db->insertID();
    }

    private function sessionFor(int $id, string $role = 'admin'): array
    {
        return ['user' => ['id' => $id, 'nip' => '100', 'nama' => 'Admin Uji', 'jabatan' => 'X', 'foto_profil' => null, 'role' => $role]];
    }

    private function asAdmin()
    {
        return $this->withSession($this->sessionFor($this->adminId));
    }

    public function testIndexMenampilkanDaftarPengguna(): void
    {
        $result = $this->asAdmin()->get('/users');

        $result->assertOK();
        $result->assertSee('Admin Uji');
    }

    public function testNonAdminDitolak(): void
    {
        $result = $this->withSession($this->sessionFor(5, 'sekretaris'))->get('/users');

        $result->assertRedirectTo('/dashboard');
    }

    public function testStoreDataValidDisimpanDenganPasswordTerhash(): void
    {
        $result = $this->asAdmin()->post('/users/store', [
            'nip' => '200', 'nama' => 'Baru', 'jabatan' => 'Dosen', 'role' => 'dosen', 'kata_sandi' => 'rahasia123',
        ]);

        $result->assertRedirectTo('/users');
        $this->seeInDatabase('users', ['nip' => '200', 'role' => 'dosen']);
        $row = $this->db->table('users')->where('nip', '200')->get()->getRowArray();
        $this->assertTrue(password_verify('rahasia123', $row['kata_sandi']));
    }

    public function testStoreDataTidakValidDitolak(): void
    {
        $result = $this->asAdmin()->post('/users/store', [
            'nip' => '100', 'nama' => '', 'role' => 'hacker', 'kata_sandi' => 'pendek',
        ]);

        $result->assertRedirectTo('/users/create');
        $result->assertSessionHas('error');
        $this->assertSame(1, $this->db->table('users')->countAllResults());
    }

    public function testUpdateMengubahRolePenggunaLain(): void
    {
        $id = $this->createUser('300', 'Lain', 'dosen');

        $result = $this->asAdmin()->post("/users/{$id}/update", [
            'nip' => '300', 'nama' => 'Lain', 'jabatan' => 'X', 'role' => 'kaprodi', 'kata_sandi' => '',
        ]);

        $result->assertRedirectTo('/users');
        $this->seeInDatabase('users', ['id' => $id, 'role' => 'kaprodi']);
    }

    public function testUpdateKataSandiKosongTidakMengubahHash(): void
    {
        $id   = $this->createUser('300', 'Lain', 'dosen');
        $hash = $this->db->table('users')->where('id', $id)->get()->getRowArray()['kata_sandi'];

        $this->asAdmin()->post("/users/{$id}/update", [
            'nip' => '300', 'nama' => 'Lain Baru', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => '',
        ]);

        $row = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        $this->assertSame('Lain Baru', $row['nama']);
        $this->assertSame($hash, $row['kata_sandi']);
    }

    public function testUpdateKataSandiBaruDiganti(): void
    {
        $id = $this->createUser('300', 'Lain', 'dosen');

        $this->asAdmin()->post("/users/{$id}/update", [
            'nip' => '300', 'nama' => 'Lain', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => 'kataSandiBaru1',
        ]);

        $row = $this->db->table('users')->where('id', $id)->get()->getRowArray();
        $this->assertTrue(password_verify('kataSandiBaru1', $row['kata_sandi']));
    }

    public function testUpdateRoleAkunSendiriDitolak(): void
    {
        $result = $this->asAdmin()->post("/users/{$this->adminId}/update", [
            'nip' => '100', 'nama' => 'Admin Uji', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => '',
        ]);

        $result->assertSessionHas('error', 'Anda tidak dapat mengubah role akun sendiri.');
        $this->seeInDatabase('users', ['id' => $this->adminId, 'role' => 'admin']);
    }

    public function testUpdateMenurunkanAdminTerakhirDitolak(): void
    {
        // Session milik admin lain yang tidak ada di DB (mis. session usang).
        $result = $this->withSession($this->sessionFor(999))->post("/users/{$this->adminId}/update", [
            'nip' => '100', 'nama' => 'Admin Uji', 'jabatan' => 'X', 'role' => 'dosen', 'kata_sandi' => '',
        ]);

        $result->assertSessionHas('error', 'Admin terakhir tidak dapat diturunkan rolenya.');
        $this->seeInDatabase('users', ['id' => $this->adminId, 'role' => 'admin']);
    }

    public function testDeleteAkunSendiriDitolak(): void
    {
        $this->createUser('300', 'Admin Kedua', 'admin');

        $result = $this->asAdmin()->post("/users/{$this->adminId}/delete");

        $result->assertSessionHas('error', 'Anda tidak dapat menghapus akun sendiri.');
        $this->seeInDatabase('users', ['id' => $this->adminId]);
    }

    public function testDeleteAdminTerakhirDitolak(): void
    {
        $result = $this->withSession($this->sessionFor(999))->post("/users/{$this->adminId}/delete");

        $result->assertSessionHas('error', 'Admin terakhir tidak dapat dihapus.');
        $this->seeInDatabase('users', ['id' => $this->adminId]);
    }

    public function testDeletePenggunaYangPunyaDataDitolak(): void
    {
        $id = $this->createUser('300', 'Sekretaris', 'sekretaris');
        $this->db->table('undangan_rapat')->insert([
            'hari' => 'Senin', 'waktu' => '2026-10-05 09:00:00', 'tempat' => 'R', 'acara' => 'A', 'created_by' => $id,
        ]);

        $result = $this->asAdmin()->post("/users/{$id}/delete");

        $result->assertSessionHas('error', 'Pengguna tidak dapat dihapus karena sudah memiliki data undangan/notulensi.');
        $this->seeInDatabase('users', ['id' => $id]);
        $this->assertSame(1, $this->db->table('undangan_rapat')->countAllResults());
    }

    public function testDeletePenggunaBiasaBerhasil(): void
    {
        $id = $this->createUser('300', 'Dosen', 'dosen');

        $result = $this->asAdmin()->post("/users/{$id}/delete");

        $result->assertRedirectTo('/users');
        $result->assertSessionHas('success', 'Pengguna berhasil dihapus.');
        $this->dontSeeInDatabase('users', ['id' => $id]);
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `vendor/bin/phpunit tests/unit/Controllers/UserControllerTest.php`
Expected: FAIL (route `/users` belum ada → 404).

- [ ] **Step 3: Implementasi**

Tambahkan di akhir `app/Config/Routes.php`:

```php

// Manajemen pengguna (khusus admin)
$routes->get('/users', 'UserController::index', ['filter' => 'role:admin']);
$routes->get('/users/create', 'UserController::create', ['filter' => 'role:admin']);
$routes->post('/users/store', 'UserController::store', ['filter' => 'role:admin']);
$routes->get('/users/(:num)/edit', 'UserController::edit/$1', ['filter' => 'role:admin']);
$routes->post('/users/(:num)/update', 'UserController::update/$1', ['filter' => 'role:admin']);
$routes->post('/users/(:num)/delete', 'UserController::delete/$1', ['filter' => 'role:admin']);
```

Buat `app/Controllers/UserController.php`:

```php
<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class UserController extends BaseController
{
    protected UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function index(): string
    {
        return view('Users/index', [
            'title' => 'Kelola Pengguna - Arsip ITD',
            'users' => $this->model->orderBy('nama', 'ASC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('Users/create', ['title' => 'Tambah Pengguna']);
    }

    public function store(): RedirectResponse
    {
        $data = $this->collectInput();

        if (! $this->validateData($data, $this->model->rulesFor(null, true))) {
            return redirect()->to('/users/create')->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $this->model->insert([
            'nip'        => $data['nip'],
            'nama'       => $data['nama'],
            'jabatan'    => $data['jabatan'],
            'role'       => $data['role'],
            'kata_sandi' => password_hash($data['kata_sandi'], PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(int $id): string|RedirectResponse
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        return view('Users/edit', ['title' => 'Edit Pengguna', 'user' => $user]);
    }

    public function update(int $id): RedirectResponse
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        $data = $this->collectInput();

        if (! $this->validateData($data, $this->model->rulesFor($id, false))) {
            return redirect()->to("/users/{$id}/edit")->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        if ($user['role'] !== $data['role']) {
            if ($id === $this->currentUserId()) {
                return redirect()->to("/users/{$id}/edit")->with('error', 'Anda tidak dapat mengubah role akun sendiri.');
            }
            if ($user['role'] === 'admin' && $this->model->countByRole('admin') <= 1) {
                return redirect()->to("/users/{$id}/edit")->with('error', 'Admin terakhir tidak dapat diturunkan rolenya.');
            }
        }

        $update = [
            'nip'     => $data['nip'],
            'nama'    => $data['nama'],
            'jabatan' => $data['jabatan'],
            'role'    => $data['role'],
        ];
        if ($data['kata_sandi'] !== '') {
            $update['kata_sandi'] = password_hash($data['kata_sandi'], PASSWORD_DEFAULT);
        }
        $this->model->update($id, $update);

        if ($id === $this->currentUserId()) {
            session()->set('user', array_merge(session()->get('user'), [
                'nip' => $data['nip'], 'nama' => $data['nama'], 'jabatan' => $data['jabatan'],
            ]));
        }

        return redirect()->to('/users')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        $user = $this->model->find($id);
        if (! $user) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        }

        if ($id === $this->currentUserId()) {
            return redirect()->to('/users')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        if ($user['role'] === 'admin' && $this->model->countByRole('admin') <= 1) {
            return redirect()->to('/users')->with('error', 'Admin terakhir tidak dapat dihapus.');
        }
        // FK created_by memakai ON DELETE CASCADE; tanpa pengecekan ini undangan/notulensi ikut terhapus.
        if ($this->model->hasRelatedData($id)) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak dapat dihapus karena sudah memiliki data undangan/notulensi.');
        }

        $this->model->delete($id);

        return redirect()->to('/users')->with('success', 'Pengguna berhasil dihapus.');
    }

    private function currentUserId(): int
    {
        return (int) (session()->get('user')['id'] ?? 0);
    }

    private function collectInput(): array
    {
        return [
            'nip'        => trim($this->request->getPost('nip') ?? ''),
            'nama'       => trim($this->request->getPost('nama') ?? ''),
            'jabatan'    => trim($this->request->getPost('jabatan') ?? ''),
            'role'       => $this->request->getPost('role') ?? '',
            'kata_sandi' => $this->request->getPost('kata_sandi') ?? '',
        ];
    }
}
```

Buat `app/Views/Users/index.php`:

```php
<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Pengguna</h3>
        <a href="/users/create" class="bg-[#1e3a5f] text-white px-4 py-2 rounded-lg hover:bg-[#2d5f8f] transition-colors">
            <i class="fas fa-plus mr-2"></i>Tambah Pengguna
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-2"></i>
                            <p>Belum ada pengguna</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $i => $u): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900"><?= $i + 1 ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($u['nip']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($u['nama']) ?></td>
                            <td class="px-6 py-4 text-sm text-gray-900"><?= esc($u['jabatan'] ?? '') ?></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= esc(\App\Models\UserModel::ROLE_LABELS[$u['role']] ?? $u['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <a href="/users/<?= $u['id'] ?>/edit" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ((int) $u['id'] !== (int) (session()->get('user')['id'] ?? 0)): ?>
                                        <form action="/users/<?= $u['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
```

Buat `app/Views/Users/create.php`:

```php
<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Tambah Pengguna</h3>

        <form action="/users/store" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                <input type="text" name="nip" required maxlength="20" value="<?= esc(old('nip')) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required maxlength="100" value="<?= esc(old('nama')) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" maxlength="100" value="<?= esc(old('jabatan')) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                    <option value="">Pilih Role</option>
                    <?php foreach (\App\Models\UserModel::ROLE_LABELS as $value => $label): ?>
                        <option value="<?= $value ?>" <?= old('role') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi <span class="text-red-500">*</span></label>
                <input type="password" name="kata_sandi" required minlength="8" autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="/users" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
```

Buat `app/Views/Users/edit.php`:

```php
<?php $isSelf = (int) $user['id'] === (int) (session()->get('user')['id'] ?? 0); ?>
<?= $this->extend('Layouts/main') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Edit Pengguna</h3>

        <form action="/users/<?= $user['id'] ?>/update" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP <span class="text-red-500">*</span></label>
                <input type="text" name="nip" required maxlength="20" value="<?= esc(old('nip', $user['nip'])) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required maxlength="100" value="<?= esc(old('nama', $user['nama'])) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                <input type="text" name="jabatan" maxlength="100" value="<?= esc(old('jabatan', $user['jabatan'] ?? '')) ?>"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                <?php if ($isSelf): ?>
                    <input type="hidden" name="role" value="<?= esc($user['role']) ?>">
                    <p class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-700">
                        <?= esc(\App\Models\UserModel::ROLE_LABELS[$user['role']] ?? $user['role']) ?>
                        <span class="text-xs text-gray-500">(role akun sendiri tidak dapat diubah)</span>
                    </p>
                <?php else: ?>
                    <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                        <?php foreach (\App\Models\UserModel::ROLE_LABELS as $value => $label): ?>
                            <option value="<?= $value ?>" <?= old('role', $user['role']) === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi Baru</label>
                <input type="password" name="kata_sandi" minlength="8" autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#1e3a5f] outline-none">
                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah. Minimal 8 karakter.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <a href="/users" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-4 py-2 bg-[#1e3a5f] text-white rounded-lg hover:bg-[#2d5f8f] transition-colors">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `vendor/bin/phpunit`
Expected: semua lulus.

- [ ] **Step 5: Checkpoint** — suite penuh hijau; lanjut ke Task 4.

---

### Task 4: Menu dan tombol menurut role

**Files:**
- Modify: `app/Views/Layouts/main.php`, `app/Views/Undangan/index.php`, `app/Views/Notulensi/index.php`, `app/Views/Notulensi/show.php`, `app/Views/Dashboard/index.php`
- Test: `tests/unit/Views/RoleVisibilityTest.php`

**Interfaces:**
- Consumes: `has_role()` (Task 2), `UserModel::ROLE_LABELS` (Task 1), route `/users` (Task 3).

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/unit/Views/RoleVisibilityTest.php`:

```php
<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Menu dan tombol harus menyesuaikan role (penjaga sesungguhnya tetap RoleFilter).
 *
 * @internal
 */
final class RoleVisibilityTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private function page(string $role, string $uri): string
    {
        $session = ['user' => ['id' => 1, 'nip' => '1', 'nama' => 'Tester', 'jabatan' => '', 'foto_profil' => null, 'role' => $role]];

        return $this->withSession($session)->get($uri)->getBody();
    }

    public function testSidebarMenuPenggunaHanyaUntukAdmin(): void
    {
        $this->assertStringContainsString('href="/users"', $this->page('admin', '/dashboard'));
        $this->assertStringNotContainsString('href="/users"', $this->page('sekretaris', '/dashboard'));
        $this->assertStringNotContainsString('href="/users"', $this->page('kaprodi', '/dashboard'));
    }

    public function testSidebarDosenTanpaMenuUndanganDanNotulensi(): void
    {
        $body = $this->page('dosen', '/dashboard');

        $this->assertStringNotContainsString('href="/undangan"', $body);
        $this->assertStringNotContainsString('href="/notulensi"', $body);
    }

    public function testSidebarMenampilkanLabelRole(): void
    {
        $this->assertStringContainsString('Ketua Program Studi', $this->page('kaprodi', '/dashboard'));
    }

    public function testTombolTambahUndanganHanyaAdminDanSekretaris(): void
    {
        $this->assertStringContainsString('Tambah Undangan', $this->page('sekretaris', '/undangan'));
        $this->assertStringContainsString('Tambah Undangan', $this->page('admin', '/undangan'));
        $this->assertStringNotContainsString('Tambah Undangan', $this->page('kaprodi', '/undangan'));
    }

    public function testTombolTambahNotulensiHanyaAdminDanSekretaris(): void
    {
        $this->assertStringContainsString('Tambah Notulensi', $this->page('sekretaris', '/notulensi'));
        $this->assertStringNotContainsString('Tambah Notulensi', $this->page('kaprodi', '/notulensi'));
    }

    public function testTombolLaporanDashboardHanyaAdminDanKaprodi(): void
    {
        $this->assertStringContainsString('Laporan Bulanan', $this->page('kaprodi', '/dashboard'));
        $this->assertStringContainsString('Laporan Bulanan', $this->page('admin', '/dashboard'));
        $this->assertStringNotContainsString('Laporan Bulanan', $this->page('sekretaris', '/dashboard'));
        $this->assertStringNotContainsString('Laporan Bulanan', $this->page('dosen', '/dashboard'));
    }

    public function testDosenTidakMelihatGrafikDashboard(): void
    {
        $this->assertStringNotContainsString('chartArsip', $this->page('dosen', '/dashboard'));
        $this->assertStringContainsString('chartArsip', $this->page('sekretaris', '/dashboard'));
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `vendor/bin/phpunit tests/unit/Views`
Expected: FAIL.

- [ ] **Step 3: Implementasi**

`app/Views/Layouts/main.php`, ganti isi `<nav>...</nav>` dengan:

```php
            <nav class="flex-1 py-4">
                <a href="/dashboard" class="nav-item flex items-center gap-3 px-6 py-3 <?= url_is('dashboard*') ? 'bg-white/20 border-r-4 border-white' : '' ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <?php if (has_role('admin', 'sekretaris', 'kaprodi')): ?>
                    <a href="/undangan" class="nav-item flex items-center gap-3 px-6 py-3 <?= url_is('undangan*') ? 'bg-white/20 border-r-4 border-white' : '' ?>">
                        <i class="fas fa-envelope w-5"></i>
                        <span>Undangan Rapat</span>
                    </a>
                    <a href="/notulensi" class="nav-item flex items-center gap-3 px-6 py-3 <?= url_is('notulensi*') ? 'bg-white/20 border-r-4 border-white' : '' ?>">
                        <i class="fas fa-file-alt w-5"></i>
                        <span>Notulensi</span>
                    </a>
                <?php endif; ?>
                <?php if (has_role('admin')): ?>
                    <a href="/users" class="nav-item flex items-center gap-3 px-6 py-3 <?= url_is('users*') ? 'bg-white/20 border-r-4 border-white' : '' ?>">
                        <i class="fas fa-users w-5"></i>
                        <span>Pengguna</span>
                    </a>
                <?php endif; ?>
            </nav>
```

Masih di layout, ganti baris `<p class="text-xs text-white/70 truncate"><?= esc(session()->get('user')['jabatan'] ?? '') ?></p>` dengan:

```php
                        <p class="text-xs text-white/70 truncate"><?= esc(\App\Models\UserModel::ROLE_LABELS[session()->get('user')['role'] ?? ''] ?? '') ?></p>
```

`app/Views/Undangan/index.php`: bungkus tombol "Tambah Undangan" (`<a href="/undangan/create" ...>...</a>`) dengan `<?php if (has_role('admin', 'sekretaris')): ?> ... <?php endif; ?>`. Di kolom aksi, bungkus ketiga aksi (download Word, edit, form hapus) dengan `<?php if (has_role('admin', 'sekretaris')): ?> ... <?php else: ?><span class="text-gray-400 text-xs">-</span><?php endif; ?>` di dalam `<div class="flex gap-2">`.

`app/Views/Notulensi/index.php`: bungkus tombol "Tambah Notulensi" dengan `has_role('admin', 'sekretaris')`; pada kolom aksi biarkan link "Lihat Detail" selalu tampil dan bungkus link Edit + form Hapus dengan `<?php if (has_role('admin', 'sekretaris')): ?> ... <?php endif; ?>`.

`app/Views/Notulensi/show.php`: bungkus link Edit (`<a href="/notulensi/<?= $notulensi['id'] ?>/edit" ...>...</a>`, sekitar baris 155) dengan `<?php if (has_role('admin', 'sekretaris')): ?> ... <?php endif; ?>`.

`app/Views/Dashboard/index.php`: bungkus seluruh blok "Chart Section" (`<div class="bg-white rounded-xl ...">` yang memuat grafik, dari komentar `<!-- Chart Section -->` sampai penutup div-nya) dengan `<?php if (has_role('admin', 'kaprodi', 'sekretaris')): ?> ... <?php endif; ?>`; di dalamnya bungkus `<div class="flex gap-2">` berisi dua tombol laporan dengan `<?php if (has_role('admin', 'kaprodi')): ?> ... <?php endif; ?>`. Periksa blok `<script>` Chart.js di bawah file: bila memakai `getElementById('chartArsip')`, bungkus script itu dengan `if` role yang sama agar tidak error di sisi browser untuk Dosen.

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `vendor/bin/phpunit`
Expected: semua lulus.

- [ ] **Step 5: Checkpoint** — suite penuh hijau; lanjut ke Task 5.

---

### Task 5: Dokumentasi dan verifikasi akhir

**Files:**
- Modify: `README.md`, `docs/superpowers/specs/2026-09-30-role-based-access-design.md`

- [ ] **Step 1: README** — di bagian Fitur Utama tambahkan butir "Hak akses berbasis peran" dan "Manajemen pengguna"; perbarui bagian "Akun Default" menjadi tabel 4 akun demo (NIP, role, kata sandi `password`) sesuai `UserSeeder`; tambahkan catatan: setelah `php spark migrate`, semua akun lama menjadi Admin dan pengguna yang sedang login harus login ulang agar role masuk session; tambahkan matriks akses dan daftar route `/users`.
- [ ] **Step 2: Spec** — tambahkan di bagian "Pengaman" (Manajemen Pengguna) satu butir: "Pengguna yang sudah memiliki undangan/notulensi tidak dapat dihapus (FK `created_by` bersifat CASCADE)."
- [ ] **Step 3: Verifikasi akhir** — jalankan `vendor/bin/phpunit`; Expected: seluruh test lulus tanpa error/failure.
