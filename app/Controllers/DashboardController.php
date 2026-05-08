<?php

namespace App\Controllers;

use App\Models\UndanganRapatModel;
use App\Models\NotulensiRapatModel;

class DashboardController extends BaseController
{
    protected UndanganRapatModel $undanganModel;
    protected NotulensiRapatModel $notulensiModel;

    public function __construct()
    {
        $this->undanganModel = new UndanganRapatModel();
        $this->notulensiModel = new NotulensiRapatModel();
    }

    public function index(): string
    {
        $bulan = (int)($this->request->getGet('bulan') ?? date('n'));
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));

        $availableYears = $this->undanganModel->getAvailableYears();
        $currentYear = date('Y');
        if (!in_array($currentYear, $availableYears)) {
            $availableYears[] = $currentYear;
        }
        sort($availableYears);
        $availableYears = array_reverse($availableYears);

        $monthlyUndangan = $this->undanganModel->countByMonth($bulan, $tahun);
        $monthlyNotulensi = $this->notulensiModel->countByMonth($bulan, $tahun);
        $yearlyUndangan = $this->undanganModel->countByYear($tahun);
        $yearlyNotulensi = $this->notulensiModel->countByYear($tahun);

        // Chart data: undangan per bulan sepanjang tahun
        $chartData = [];
        $namaBulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = [
                'bulan' => $namaBulan[$m-1],
                'undangan' => $this->undanganModel->countByMonth($m, $tahun),
                'notulensi' => $this->notulensiModel->countByMonth($m, $tahun),
            ];
        }

        return view('Dashboard/index', [
            'title' => 'Dashboard - Arsip Rapat ITD',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'availableYears' => $availableYears,
            'monthlyUndangan' => $monthlyUndangan,
            'monthlyNotulensi' => $monthlyNotulensi,
            'yearlyUndangan' => $yearlyUndangan,
            'yearlyNotulensi' => $yearlyNotulensi,
            'chartData' => $chartData,
        ]);
    }

    public function downloadLaporan(): \CodeIgniter\HTTP\ResponseInterface
    {
        $type = $this->request->getGet('type') ?? 'bulanan';
        $bulan = (int)($this->request->getGet('bulan') ?? date('n'));
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));

        if ($type === 'bulanan') {
            $undanganList = $this->undanganModel->findByMonth($bulan, $tahun);
            $notulensiList = $this->notulensiModel->findByMonth($bulan, $tahun);
            $periodLabel = $this->getNamaBulan($bulan) . ' ' . $tahun;
        } else {
            $undanganList = $this->undanganModel->findByYear($tahun);
            $notulensiList = $this->notulensiModel->findByYear($tahun);
            $periodLabel = 'Tahun ' . $tahun;
        }

        // Generate PDF menggunakan HTML
        $html = $this->generateLaporanHTML($periodLabel, $undanganList, $notulensiList, $type);

        return $this->response
            ->setContentType('text/html; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="laporan_rapat_' . $type . '_' . ($type === 'bulanan' ? $bulan . '_' : '') . $tahun . '.html"')
            ->setBody($html);
    }

    private function generateLaporanHTML(string $period, array $undangan, array $notulensi, string $type): string
    {
        $namaUser = session()->get('user')['nama'] ?? 'Admin';
        $tanggalCetak = date('d F Y');

        $undanganRows = '';
        foreach ($undangan as $i => $u) {
            $waktu = date('d/m/Y H:i', strtotime($u['waktu']));
            $undanganRows .= "<tr>
                <td>" . ($i+1) . "</td>
                <td>{$u['hari']}</td>
                <td>{$waktu}</td>
                <td>{$u['tempat']}</td>
                <td>{$u['acara']}</td>
            </tr>";
        }

        $notulensiRows = '';
        foreach ($notulensi as $i => $n) {
            $tgl = date('d/m/Y', strtotime($n['tgl_rapat']));
            $notulensiRows .= "<tr>
                <td>" . ($i+1) . "</td>
                <td>{$tgl}</td>
                <td>{$n['tema_rapat']}</td>
                <td>{$n['nama_undangan']}</td>
                <td>{$n['deskripsi_rapat']}</td>
            </tr>";
        }

        return "<!DOCTYPE html>
<html lang='id'>
<head>
<meta charset='UTF-8'>
<title>Laporan Rapat - {$period}</title>
<style>
  body { font-family: Arial, sans-serif; margin: 40px; color: #333; }
  .header { text-align: center; border-bottom: 3px double #1e3a5f; padding-bottom: 20px; margin-bottom: 30px; }
  .header h1 { color: #1e3a5f; font-size: 18px; margin: 0; }
  .header h2 { color: #1e3a5f; font-size: 16px; margin: 5px 0; }
  .header p { margin: 3px 0; font-size: 13px; }
  .section-title { background: #1e3a5f; color: white; padding: 8px 15px; font-size: 14px; margin: 20px 0 10px; }
  table { width: 100%; border-collapse: collapse; font-size: 12px; }
  th { background: #2d5f8f; color: white; padding: 8px; text-align: left; }
  td { padding: 7px 8px; border-bottom: 1px solid #ddd; }
  tr:nth-child(even) { background: #f5f9ff; }
  .summary { display: flex; gap: 20px; margin-bottom: 20px; }
  .summary-box { flex: 1; border: 1px solid #2d5f8f; padding: 15px; text-align: center; border-radius: 5px; }
  .summary-box .number { font-size: 32px; font-weight: bold; color: #1e3a5f; }
  .summary-box .label { font-size: 12px; color: #666; }
  .footer { margin-top: 40px; text-align: right; font-size: 12px; color: #666; }
  @media print { body { margin: 20px; } }
</style>
</head>
<body>
<div class='header'>
  <h1>PROGRAM STUDI ITD ADISUTJIPTO</h1>
  <h2>LAPORAN ARSIP RAPAT - " . strtoupper($period) . "</h2>
  <p>Dicetak oleh: {$namaUser} | Tanggal: {$tanggalCetak}</p>
</div>

<div class='summary'>
  <div class='summary-box'>
    <div class='number'>" . count($undangan) . "</div>
    <div class='label'>Total Undangan Rapat</div>
  </div>
  <div class='summary-box'>
    <div class='number'>" . count($notulensi) . "</div>
    <div class='label'>Total Notulensi Rapat</div>
  </div>
</div>

<div class='section-title'>DAFTAR UNDANGAN RAPAT</div>
<table>
  <thead><tr><th>No</th><th>Hari</th><th>Waktu</th><th>Tempat</th><th>Acara</th></tr></thead>
  <tbody>{$undanganRows}</tbody>
</table>

<div class='section-title'>DAFTAR NOTULENSI RAPAT</div>
<table>
  <thead><tr><th>No</th><th>Tgl Rapat</th><th>Tema</th><th>Undangan Terkait</th><th>Deskripsi</th></tr></thead>
  <tbody>{$notulensiRows}</tbody>
</table>

<div class='footer'>
  <p>Laporan ini digenerate secara otomatis oleh Sistem Informasi Arsip Rapat ITD Adisutjipto</p>
</div>
<script>window.onload = function() { window.print(); }</script>
</body></html>";
    }

    private function getNamaBulan(int $bulan): string
    {
        $nama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        return $nama[$bulan] ?? '';
    }
}
